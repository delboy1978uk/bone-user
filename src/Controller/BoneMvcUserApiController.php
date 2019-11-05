<?php

declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser\Controller;

use Del\Form\Form;
use Bone\Mvc\Controller;
use BoneMvc\Mail\Service\MailService;
use BoneMvc\Module\BoneMvcUser\Form\PersonForm;
use Del\Entity\User;
use Del\Form\Field\FileUpload;
use Del\Image;
use Del\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class BoneMvcUserApiController
{
    /** @var UserService $userService */
    private $userService;

    /**
     * BoneMvcUserController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
        $file->setUploadDirectory('/tmp');
        $form->addField($file);

        if ($form->isValid($data)) {

            try {
                $data = $form->getValues();
                /** @var Zend_Form_Element_File $file */
                $file = $data['avatar'];
                $src = '/tmp/' . $file;
                $destinationFileName = $this->getFilename($file);

                $fullPath = 'data/uploads/img' . DIRECTORY_SEPARATOR . $destinationFileName;
                $contents = file_get_contents($src);
                file_put_contents($fullPath, $contents);
                chmod($fullPath, 0775);

                $image = new Image($fullPath);
                if ($image->getHeight() > $image->getWidth()) { //portrait

                    $image->resizeToWidth(75);
                    $image->crop(75, 75);

                } elseif ($image->getHeight() < $image->getWidth()) { //landscape

                    $image->resizeToHeight(75);
                    $image->crop(75, 75);

                } else { //square

                    $image->resize(75, 75);

                }
                $image->save();

                /** @var User $user */
                $user = $request->getAttribute('user');
                $person = $user->getPerson();
                $person->setImage($destinationFileName);
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
     * @param Zend_Form_Element_File $file
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
}
