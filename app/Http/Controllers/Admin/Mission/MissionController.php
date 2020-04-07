<?php
namespace App\Http\Controllers\Admin\Mission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\MissionMedia\MissionMediaRepository;
use App\Helpers\ResponseHelper;
use Validator;
use App\Traits\RestExceptionHandlerTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use App\Exceptions\TenantDomainNotFoundException;
use App\Events\User\UserNotificationEvent;
use App\Events\User\UserActivityLogEvent;
use App\Helpers\LanguageHelper;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;
use App\Repositories\City\CityRepository;

//!  Mission controller
/*!
This controller is responsible for handling mission listing, show, store, update and delete operations.
 */
class MissionController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    private $missionRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var string
     */
    private $userApiKey;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Repositories\MissionMedia\MissionMediaRepository
     */
    private $missionMediaRepository;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * @var App\Repositories\City\CityRepository
     */
    private $cityRepository;

    /**
     * Create a new controller instance.
     *
     * @param  App\Repositories\Mission\MissionRepository $missionRepository
     * @param  App\Helpers\ResponseHelper $responseHelper
     * @param Illuminate\Http\Request $request
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Repositories\MissionMedia\MissionMediaRepository $missionMediaRepository
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @param App\Repositories\City\CityRepository $CityRepository
     * @return void
     */
    public function __construct(
        MissionRepository $missionRepository,
        ResponseHelper $responseHelper,
        Request $request,
        LanguageHelper $languageHelper,
        MissionMediaRepository $missionMediaRepository,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository,
        CityRepository $cityRepository
    ) {
        $this->missionRepository = $missionRepository;
        $this->responseHelper = $responseHelper;
        $this->userApiKey = $request->header('php-auth-user');
        $this->languageHelper = $languageHelper;
        $this->missionMediaRepository = $missionMediaRepository;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
        $this->cityRepository =  $cityRepository;
    }

    /**
     * Display a listing of Mission.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get mission
            $missions = $this->missionRepository->missionList($request);

            // Set response data
            $apiData = $missions;
            $apiStatus = Response::HTTP_OK;
            $apiMessage = ($missions->isEmpty()) ? trans('messages.success.MESSAGE_NO_RECORD_FOUND')
             : trans('messages.success.MESSAGE_MISSION_LISTING');
            return $this->responseHelper->successWithPagination($apiStatus, $apiMessage, $apiData);
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Server side validataions
        $validator = Validator::make(
            $request->all(),
            [
                "theme_id" => "integer|exists:mission_theme,mission_theme_id,deleted_at,NULL",
                "mission_type" => ['required', Rule::in(config('constants.mission_type'))],
                "location" => "required",
                "location.city_id" => "integer|required|exists:city,city_id,deleted_at,NULL",
                "location.country_code" => "required|exists:country,ISO,deleted_at,NULL",
                "availability_id" => "integer|required|exists:availability,availability_id,deleted_at,NULL",
                "mission_detail" => "required",
                "mission_detail.*.lang" => "required|max:2",
                "mission_detail.*.title" => "required",
                "mission_detail.*.section" => "required",
                "mission_detail.*.section.*.title" => "required_with:mission_detail.*.section",
                "mission_detail.*.section.*.description" =>
                "required_with:mission_detail.*.section",
                "organisation" => "required",
                "organisation.organisation_id" => "required",
                "organisation.organisation_name" => "required",
                "publication_status" => ['required', Rule::in(config('constants.publication_status'))],
                "media_images.*.media_path" => "required|valid_media_path",
                "media_videos.*.media_name" => "required",
                "media_videos.*.media_path" => "required|valid_video_url",
                "documents.*.document_path" => "required|valid_document_path",
                "start_date" => "required_if:mission_type,TIME|required_with:end_date|date",
                "end_date" => "sometimes|after:start_date|date",
                "total_seats" => "integer|min:1",
                "goal_objective" => "required_if:mission_type,GOAL|integer|min:1",
                "skills.*.skill_id" => "integer|exists:skill,skill_id,deleted_at,NULL",
                "mission_detail.*.short_description" => "max:1000",
                "mission_detail.*.custom_information" =>"nullable",
                "mission_detail.*.custom_information.*.title" => "required_with:mission_detail.*.custom_information",
                "mission_detail.*.custom_information.*.description" =>
                "required_with:mission_detail.*.custom_information",
                "media_images.*.sort_order" => "required|numeric|min:0|not_in:0",
                "media_videos.*.sort_order" => "required|numeric|min:0|not_in:0",
                "documents.*.sort_order" => "required|numeric|min:0|not_in:0",
                "is_virtual" => "sometimes|required|in:0,1",
                "mission_detail.*.label_goal_achieved" => 'sometimes|required_if:mission_type,GOAL|max:255',
                "mission_detail.*.label_goal_objective" => 'sometimes|required_if:mission_type,GOAL|max:255'
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_MISSION_DATA'),
                $validator->errors()->first()
            );
        }

        $city = $this->cityRepository->getState($request->location['city_id']);
        $request->request->add(['state_id' => $city[0]['state_id']]);

        $mission = $this->missionRepository->store($request);

        // Set response data
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_ADDED');
        $apiData = ['mission_id' => $mission->mission_id];

        // Send notification to user if mission publication status is PUBLISHED
        if ($mission->publication_status === config('constants.publication_status.APPROVED') ||
            $mission->publication_status === config('constants.publication_status.PUBLISHED_FOR_APPLYING')
        ) {
            // Send notification to all users
            $notificationType = config('constants.notification_type_keys.NEW_MISSIONS');
            $entityId = $mission->mission_id;
            $action = config('constants.notification_actions.CREATED');
            event(new UserNotificationEvent($notificationType, $entityId, $action));
        }

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.MISSION'),
            config('constants.activity_log_actions.CREATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $mission->mission_id
        ));
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Display the specified mission detail.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Get data for parent table
            $mission = $this->missionRepository->find($id);

            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_MISSION_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $mission->toArray());
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_NO_MISSION_FOUND'),
                trans('messages.custom_error_message.ERROR_NO_MISSION_FOUND')
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $this->missionRepository->find($id);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            );
        }

        // Server side validataions
        $validator = Validator::make(
            $request->all(),
            [
                "mission_type" => [Rule::in(config('constants.mission_type'))],
                "location.city_id" => "required_with:location|integer|exists:city,city_id,deleted_at,NULL",
                "location.country_code" => "required_with:location|exists:country,ISO",
                "mission_detail.*.lang" => "required_with:mission_detail|max:2",
                "mission_detail.*.title" => "sometimes|required",
                "publication_status" => [Rule::in(config('constants.publication_status'))],
                "goal_objective" => "required_if:mission_type,GOAL|integer|min:1",
                "start_date" => "sometimes|required_if:mission_type,TIME,required_with:end_date|date",
                "end_date" => "sometimes|after:start_date|date",
                "total_seats" => "integer|min:1",
                "availability_id" => "sometimes|required|integer|exists:availability,availability_id,deleted_at,NULL",
                "skills.*.skill_id" => "integer|exists:skill,skill_id,deleted_at,NULL",
                "theme_id" => "sometimes|integer|exists:mission_theme,mission_theme_id,deleted_at,NULL",
                "application_deadline" => "date",
                "mission_detail.*.short_description" => "max:1000",
                "mission_detail.*.custom_information" =>"nullable",
                "mission_detail.*.custom_information.*.title" => "required_with:mission_detail.*.custom_information",
                "mission_detail.*.custom_information.*.description" =>
                "required_with:mission_detail.*.custom_information",
                "media_images.*.media_path" => "sometimes|required|valid_media_path",
                "media_videos.*.media_name" => "sometimes|required",
                "media_videos.*.media_path" => "sometimes|required|valid_video_url",
                "documents.*.document_path" => "sometimes|required|valid_document_path",
                "organisation.organisation_id" => "sometimes|required",
                "organisation.organisation_name" => "sometimes|required",
                "media_images.*.sort_order" => "sometimes|required|numeric|min:0|not_in:0",
                "media_videos.*.sort_order" => "sometimes|required|numeric|min:0|not_in:0",
                "documents.*.sort_order" => "sometimes|required|numeric|min:0|not_in:0",
                "is_virtual" => "sometimes|required|in:0,1",
                "mission_detail.*.label_goal_achieved" => 'sometimes|required_if:mission_type,GOAL|max:255',
                "mission_detail.*.label_goal_objective" => 'sometimes|required_if:mission_type,GOAL|max:255'
            ]
        );

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_MISSION_REQUIRED_FIELDS_EMPTY'),
                $validator->errors()->first()
            );
        }
        
        $city = $this->cityRepository->getState($request->location['city_id']);
        $request->request->add(['state_id' =>  $city[0]['state_id']]);
 
        try {
            if (isset($request->media_images) && count($request->media_images) > 0) {
                foreach ($request->media_images as $mediaImages) {
                    if (isset($mediaImages['media_id']) && ($mediaImages['media_id'] !== "")) {
                        $this->missionMediaRepository->find($mediaImages['media_id']);
                        $mediaImage = $this->missionMediaRepository->isMediaLinkedToMission(
                            $mediaImages['media_id'],
                            $id
                        );
                        if (!$mediaImage) {
                            return $this->responseHelper->error(
                                Response::HTTP_UNPROCESSABLE_ENTITY,
                                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                                config('constants.error_codes.ERROR_MISSION_REQUIRED_FIELDS_EMPTY'),
                                trans('messages.custom_error_message.ERROR_MEDIA_NOT_LINKED_WITH_MISSION')
                            );
                        }
                    }
                }
            }

            if (isset($request->media_videos) && count($request->media_videos) > 0) {
                foreach ($request->media_videos as $mediaVideos) {
                    if (isset($mediaVideos['media_id']) && ($mediaVideos['media_id'] != "")) {
                        $this->missionMediaRepository->find($mediaVideos['media_id']);
                        $mediaVideo = $this->missionMediaRepository->isMediaLinkedToMission(
                            $mediaVideos['media_id'],
                            $id
                        );
                        if (!$mediaVideo) {
                            return $this->responseHelper->error(
                                Response::HTTP_UNPROCESSABLE_ENTITY,
                                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                                config('constants.error_codes.ERROR_MISSION_REQUIRED_FIELDS_EMPTY'),
                                trans('messages.custom_error_message.ERROR_MEDIA_NOT_LINKED_WITH_MISSION')
                            );
                        }
                    }
                }
            }
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MEDIA_ID_DOSENT_EXIST'),
                trans('messages.custom_error_message.ERROR_MEDIA_ID_DOSENT_EXIST')
            );
        }

        try {
            if (isset($request->documents) && count($request->documents) > 0) {
                foreach ($request->documents as $mediaDocuments) {
                    if (isset($mediaDocuments['document_id']) && ($mediaDocuments['document_id'] !== "")) {
                        $this->missionRepository->findDocument($mediaDocuments['document_id']);
                        $mediaDocument = $this->missionRepository->isDocumentLinkedToMission(
                            $mediaDocuments['document_id'],
                            $id
                        );
                        if (!$mediaDocument) {
                            return $this->responseHelper->error(
                                Response::HTTP_UNPROCESSABLE_ENTITY,
                                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                                config('constants.error_codes.ERROR_MISSION_REQUIRED_FIELDS_EMPTY'),
                                trans('messages.custom_error_message.ERROR_DOCUMENT_NOT_LINKED_WITH_MISSION')
                            );
                        }
                    }
                }
            }
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_DOCUMENT_ID_DOSENT_EXIST'),
                trans('messages.custom_error_message.ERROR_DOCUMENT_ID_DOSENT_EXIST')
            );
        }

        $language = $this->languageHelper->getDefaultTenantLanguage($request);
        $missionDetails = $this->missionRepository->getMissionDetailsFromId($id, $language->language_id);

        // Check for default language delete
        if (isset($request->mission_detail)) {
            foreach ($request->mission_detail as $value) {
                if (array_key_exists('section', $value)) {
                    if (empty($value['section'])) {
                        if ($value['lang'] === $language->code) {
                            return $this->responseHelper->error(
                                Response::HTTP_UNPROCESSABLE_ENTITY,
                                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                                config('constants.error_codes.ERROR_MISSION_DEFAULT_LANGUAGE_CANNOT_DELETED'),
                                trans('messages.custom_error_message.ERROR_MISSION_DEFAULT_LANGUAGE_CANNOT_DELETED')
                            );
                        }
                    }
                }
            }
        }

        $this->missionRepository->update($request, $id);

        // Set response data
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_MISSION_UPDATED');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.MISSION'),
            config('constants.activity_log_actions.UPDATED'),
            config('constants.activity_log_user_types.API'),
            $this->userApiKey,
            get_class($this),
            $request->toArray(),
            null,
            $id
        ));

        // Send notification to user if mission publication status is PUBLISHED
        $approved = config('constants.publication_status.APPROVED');
        $publishedForApplying = config('constants.publication_status.PUBLISHED_FOR_APPLYING');
        if ((($request->publication_status !== $missionDetails->publication_status) &&
        ($request->publication_status === $approved || $request->publication_status === $publishedForApplying))
        ) {
            // Send notification to all users
            $notificationType = config('constants.notification_type_keys.NEW_MISSIONS');
            $entityId = $id;
            $action = config('constants.notification_actions.'.$request->publication_status);

            event(new UserNotificationEvent($notificationType, $entityId, $action));
        }
        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $mission = $this->missionRepository->delete($id);

            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_MISSION_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MISSION'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                null,
                null,
                $id
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the mission media from storage.
     *
     * @param int $mediaId
     * @return Illuminate\Http\JsonResponse
     */
    public function removeMissionMedia(int $mediaId): JsonResponse
    {
        try {
            $this->missionRepository->deleteMissionMedia($mediaId);
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_MISSION_MEDIA_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MISSION_MEDIA'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                null,
                null,
                $mediaId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_MEDIA_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_MEDIA_NOT_FOUND')
            );
        }
    }

    /**
     * Remove the mission document from storage.
     *
     * @param int $documentId
     * @return Illuminate\Http\JsonResponse
     */
    public function removeMissionDocument(int $documentId): JsonResponse
    {
        try {
            $this->missionRepository->deleteMissionDocument($documentId);
            $apiStatus = Response::HTTP_NO_CONTENT;
            $apiMessage = trans('messages.success.MESSAGE_MISSION_DOCUMENT_DELETED');

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.MISSION_DOCUMENT'),
                config('constants.activity_log_actions.DELETED'),
                config('constants.activity_log_user_types.API'),
                $this->userApiKey,
                get_class($this),
                null,
                null,
                $documentId
            ));

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_MISSION_DOCUMENT_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_MISSION_DOCUMENT_NOT_FOUND')
            );
        }
    }
}
