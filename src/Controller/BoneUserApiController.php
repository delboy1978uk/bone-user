<?php

declare(strict_types=1);

namespace Bone\User\Controller;

use Bone\Http\Response;
use Bone\Http\Response\HtmlResponse;
use Bone\I18n\I18nAwareInterface;
use Bone\I18n\Traits\HasTranslatorTrait;
use Bone\Mail\EmailMessage;
use Bone\Server\SiteConfig;
use Bone\User\Form\RegistrationForm;
use DateTime;
use Del\Entity\Country;
use Del\Exception\UserException;
use Del\Factory\CountryFactory;
use Del\Form\Form;
use Exception;
use Bone\Controller\Controller;
use Bone\Mail\Service\MailService;
use Bone\User\Form\PersonForm;
use Del\Entity\User;
use Del\Form\Field\FileUpload;
use Del\Image;
use Del\Service\UserService;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class BoneUserApiController extends Controller
{
    public function __construct(
        private UserService $userService, 
        private string $uploadsDirectory, 
        private string $imgSubDirectory, 
        private string $tempDirectory, 
        private MailService $mailService
    ) {}

    public function chooseAvatarAction(ServerRequestInterface $request): ResponseInterface
    {
        $avatar = $request->getParsedBody()['avatar'];
        /** @var User $user */
        $user = $request->getAttribute('user');
        $person = $user->getPerson();
        $image = new Image('public' . $avatar);
        $avatar = str_replace('/bone-user/img/avatars/', '', $avatar);
        $file = $this->imgSubDirectory . $this->getFilename($avatar);
        $image->save($this->uploadsDirectory . $file);
        $person->setImage($file);
        $this->userService->getPersonService()->savePerson($person);

        return new JsonResponse([
            'result' => 'success',
            'message' => 'Avatar is now set to ' . $avatar . '.',
            'avatar' => $avatar,
        ]);
    }

    public function uploadAvatarAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $form = new Form('upload');
        $file = new FileUpload('avatar');
        $file->setUploadDirectory($this->tempDirectory);
        $file->setRequired(true);
        $form->addField($file);

        if ($form->isValid($data)) {

            try {
                $data = $form->getValues();
                $file = $data['avatar'];
                $sourceFileName = $this->tempDirectory . $file;
                $newFileName = $this->imgSubDirectory . $this->getFilename($file);
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
                $this->userService->getPersonService()->savePerson($person);

                return new JsonResponse([
                    'result' => 'success',
                    'message' => 'Avatar now set to ' . $person->getImage(),
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

    private function getFilename(string $fileNameAsUploaded): string
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

    public function avatar(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();
        /** @var User $user */
        $user = $request->getAttribute('user');

        if ($img = $user->getPerson()->getImage()) {
            $path = $this->uploadsDirectory . $img;
            $mimeType = $this->getMimeType($path);
        }


        $contents = file_get_contents($path);
        $stream = new Stream('php://memory', 'r+');
        $stream->write($contents);
        $response = $response->withBody($stream);
        $response = $response->withHeader('Content-Type', $mimeType);

        return $response;
    }

    private function getMimeType(string $path): string
    {
        $finfo = finfo_open(FILEINFO_MIME); // return mime type
        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        return $mimeType;
    }
}
