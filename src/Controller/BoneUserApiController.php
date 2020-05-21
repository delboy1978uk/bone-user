<?php

declare(strict_types=1);

namespace Bone\User\Controller;

use Bone\Server\SiteConfig;
use Del\Form\Form;
use Bone\Controller\Controller;
use Bone\Mail\Service\MailService;
use Bone\User\Form\PersonForm;
use Del\Entity\User;
use Del\Form\Field\FileUpload;
use Del\Image;
use Del\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class BoneUserApiController
{
    /** @var UserService $userService */
    private $userService;

    /** @var string $uploadsDirectory */
    private $uploadsDirectory;

    /** @var string $tempDirectory */
    private $tempDirectory;

    /** @var string $imgDirectory */
    private $imgDirectory;

    /**
     * BoneUserController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService, string $uploadsDirectory, string $imgSubDir, string $tempDirectory)
    {
        $this->userService = $userService;
        $this->uploadsDirectory = $uploadsDirectory;
        $this->tempDirectory = $tempDirectory;
        $this->imgDirectory = $imgSubDir;
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function chooseAvatarAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $avatar = $request->getParsedBody()['avatar'];
        /** @var User $user */
        $user = $request->getAttribute('user');
        $person = $user->getPerson();
        $person->setImage($avatar);
        $this->userService->getPersonSvc()->savePerson($person);

        return new JsonResponse([
            'result' => 'success',
            'message' => 'Avatar is now set to ' . $avatar . '.',
            'avatar' => $avatar,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function uploadAvatarAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $form = new Form('upload');
        $file = new FileUpload('avatar');
        $file->setUploadDirectory($this->tempDirectory);
        $form->addField($file);

        if ($form->isValid($data)) {

            try {
                $data = $form->getValues();
                $file = $data['avatar'];
                $sourceFileName = $this->tempDirectory . $file;
                $newFileName = $this->imgDirectory . $this->getFilename($file);
                $destinationFileName = $this->uploadsDirectory . $newFileName;
                $image = new Image($sourceFileName);
                
                if ($image->getHeight() > $image->getWidth()) { //portrait

                    $image->resizeToWidth(100);
                    $image->crop(100, 100);

                } elseif ($image->getHeight() < $image->getWidth()) { //landscape

                    $image->resizeToHeight(100);
                    $image->crop(100, 100);

                } else { //square

                    $image->resize(100, 100);

                }
                $image->save($destinationFileName, 0775);
                unlink($sourceFileName);

                /** @var User $user */
                $user = $request->getAttribute('user');
                $person = $user->getPerson();
                $person->setImage($newFileName);
                $this->userService->getPersonSvc()->savePerson($person);

                return new JsonResponse([
                    'result' => 'success',
                    'message' => 'Avatar now set to '.$person->getImage(),
                    'avatar' => $person->getImage(),
                ]);
            } catch (Exception $e) {
                return new JsonResponse([
                    'result' => 'danger',
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return new JsonResponse([
            'result' => 'danger',
            'message' => 'There was a problem with your upload.',
        ]);
    }

    /**
     * @param string $fileNameAsUploaded
     * @return string
     */
    private function getFilename(string $fileNameAsUploaded)
    {
        // break the filename up on dots
        $filenameParts = explode('.', $fileNameAsUploaded);

        // Create an array of encoded parts
        $encoded = [];

        // Loop through each part
        foreach ($filenameParts as $filenamePart) {
            // Url encode the filename part
            $encoded[] = urlencode($filenamePart);
        }

        // Create a little uniqueness, in case they upload a file with the same name
        $unique = dechex(time());

        // Pop off the last part (file extension)
        $ext = array_pop($encoded);

        // Push on the unique part
        $encoded[] = $unique;

        // Piece the encoded filename together
        $filenameOnDisk = implode('_', $encoded);

        // Add the extension
        $filenameOnDisk .= '.' . $ext;

        return $filenameOnDisk;
    }



    /**
     * User profile data
     * @OA\Get(
     *     path="/api/user/profile",
     *     @OA\Response(response="200", description="User profile data"),
     *     tags={"user"},
     *     security={
     *         {"oauth2": {"basic"}}
     *     }
     * )
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function profileAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $person = $user->getPerson();
        $country = $person->getCountry();
        $user = $this->userService->toArray($user);
        $dob = $person->getDob() ? $person->getDob()->format('Y-m-d H:i:s') : null;
        $person = $this->userService->getPersonSvc()->toArray($person);
        $person['dob'] = $dob;
        $person['country'] = $country ? $country->toArray() : null;
        $user['person'] = $person;
        unset($user['password']);

        return new JsonResponse($user);
    }
}
