<?php

declare(strict_types=1);

namespace Bone\User\Controller;

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
use Bone\Controller\Controller;
use Bone\Mail\Service\MailService;
use Bone\User\Form\PersonForm;
use Del\Entity\User;
use Del\Form\Field\FileUpload;
use Del\Image;
use Del\Service\UserService;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class BoneUserApiController extends Controller
{
    /** @var UserService $userService */
    private $userService;

    /** @var string $uploadsDirectory */
    private $uploadsDirectory;

    /** @var string $tempDirectory */
    private $tempDirectory;

    /** @var string $imgDirectory */
    private $imgDirectory;

    /** @var MailService $mailService */
    private $mailService;

    /**
     * BoneUserController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService, string $uploadsDirectory, string $imgSubDir, string $tempDirectory, MailService $mailService)
    {
        $this->userService = $userService;
        $this->uploadsDirectory = $uploadsDirectory;
        $this->tempDirectory = $tempDirectory;
        $this->imgDirectory = $imgSubDir;
        $this->mailService = $mailService;
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
     * User profile data.
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


    /**
     * Register a new user.
     * @OA\Post(
     *     path="/api/user/register",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "password", "confirm"},
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="fake@email.com",
     *                     description="The new user's email"
     *                 ),@OA\Property(
     *                     property="password",
     *                     type="string",
     *                     example="xxxxxxxxxx",
     *                     description="The users chosen password"
     *                 ),@OA\Property(
     *                     property="confirm",
     *                     type="string",
     *                     example="xxxxxxxxxx",
     *                     description="Password confirmation"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Email sent"),
     *     tags={"user"},
     *     security={
     *         {"oauth2": {"register"}}
     *     }
     * )
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function registerAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $form = new RegistrationForm('register', $this->getTranslator());
        $message = null;
        $responseData = [];

        $formData = $request->getParsedBody();
        $form->populate($formData);

        if ($form->isValid()) {
            $data = $form->getValues();
            try {
                $user = $this->userService->registerUser($data);
                $link = $this->userService->generateEmailLink($user);
                $mail = $this->mailService;

                $env = $mail->getSiteConfig()->getEnvironment();
                $email = $user->getEmail();
                $token = $link->getToken();

                $mail = new EmailMessage();
                $mail->setTo($user->getEmail());
                $mail->setSubject($this->getTranslator()->translate('email.user.register.thankswith', 'user') . ' ' . $this->mailService->getSiteConfig()->getTitle());
                $mail->setTemplate('email.user::user_registration/user_registration');
                $mail->setViewData([
                    'siteUrl' => $env->getSiteURL(),
                    'logo' => $this->getSiteConfig()->getEmailLogo(),
                    'activationLink' => '/user/activate/' . $email . '/' . $token,
                ]);
                $this->mailService->sendEmail($mail);

                $responseData['success'] = 'Email sent to ' . $email;
                $code = 200;

            } catch (UserException $e) {
                $responseData['error'] = $e->getMessage();
                $responseData['code'] = $e->getCode();
            }
        } else {
            $responseData['error'] = $form->getErrorMessages();
        }

        return new JsonResponse($responseData);
    }

    /**
     * Update user profile data.
     * @OA\Put(
     *     path="/api/user/profile",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "password", "confirm"},
     *                 @OA\Property(
     *                     property="firstname",
     *                     type="string",
     *                     example="Captain",
     *                     description="The user's firstname"
     *                 ),@OA\Property(
     *                     property="middlename",
     *                     type="string",
     *                     example="Jack",
     *                     description="The users middlename"
     *                 ),@OA\Property(
     *                     property="lastname",
     *                     type="string",
     *                     example="Sparrow",
     *                     description="The user's surname"
     *                 ),
     *                  @OA\Property(
     *                     property="aka",
     *                     type="string",
     *                     example="outlaw pirate",
     *                     description="The user's nickname"
     *                 ),
     *                  @OA\Property(
     *                     property="dob",
     *                     type="date",
     *                     example="2014-09-18",
     *                     description="The user's date of birth"
     *                 ),
     *                  @OA\Property(
     *                     property="birthplace",
     *                     type="string",
     *                     example="Jamaica",
     *                     description="The user's birthplace"
     *                 ),
     *                  @OA\Property(
     *                     property="country",
     *                     type="string",
     *                     example="JM",
     *                     description="The user's country"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success message"),
     *     tags={"user"},
     *     security={
     *         {"oauth2": {"basic"}}
     *     }
     * )
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function editProfileAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $form = new PersonForm('profile', $this->getTranslator());
        $form->populate($data);

        if ($form->isValid()) {
            $data = $form->getValues();
            $data['dob'] = new DateTime($data['dob']);
            $data['country'] = CountryFactory::generate($data['country']);
            $user = $request->getAttribute('user');
            $person = $user->getPerson();
            $personService = $this->userService->getPersonSvc();
            $person = $personService->populateFromArray($person, $data);
            $person = $personService->toArray($person);
            $person['country'] = $person['country']->toArray();

            return new JsonResponse($person);
        }

        return new JsonResponse($form->getErrorMessages());
    }
}
