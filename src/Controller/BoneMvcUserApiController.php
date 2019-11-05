<?php

declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser\Controller;

use Bone\Mvc\Controller;
use BoneMvc\Mail\Service\MailService;
use BoneMvc\Module\BoneMvcUser\Form\PersonForm;
use Del\Entity\User;
use Del\Form\Field\FileUpload;
use Del\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class BoneMvcUserApiController extends Controller
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
        $data = $this->getRequest()->getPost();

        $form = new Form();
        $file = new FileUpload('avatar');
        $file->setUploadDirectory(TMP_PATH);
        $form->addElement($file);

        if ($form->isValid($data)) {

            try {

                /** @var Zend_Form_Element_File $file */
                $file = $form->avatar;
                $src = $file->getFileName();
                $destinationFileName = $this->getFilename($file);

                $adapter = new Local(DIRECTORY_SEPARATOR);
                $filesystem = new Filesystem($adapter);

                $fullPath = UPLOAD_PATH . DIRECTORY_SEPARATOR . $destinationFileName;

                $filesystem->copy($src, $fullPath);

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

                $person = $this->getUsersPerson();
                $person->setImage($destinationFileName);
                $person = $this->getPersonService()->savePerson($person);

                $this->sendJSONResponse([
                    'result' => 'success',
                    'message' => 'Avatar now set to '.$person->getImage(),
                    'avatar' => $person->getImage(),
                ]);
            } catch (Exception $e) {
                $this->sendJSONResponse([
                    'result' => 'danger',
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $this->sendJSONResponse([
            'result' => 'danger',
            'message' => 'There was a problem with your upload.',
        ]);
    }
}
