<?php

namespace App\Http\Controllers\App\Timesheet;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Mission\MissionRepository;
use App\Repositories\Timesheet\TimesheetRepository;
use App\Traits\RestExceptionHandlerTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use PDOException;
use Validator;

class TimesheetController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\Timesheet\TimesheetRepository
     */
    private $timesheetRepository;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Repositories\Mission\MissionRepository
     */
    private $missionRepository;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\Timesheet\TimesheetRepository $timesheetRepository
     * @param App\Helpers\ResponseHelper $responseHelper
     * @param App\Repositories\Mission\MissionRepository $missionRepository
     *
     * @return void
     */
    public function __construct(
        TimesheetRepository $timesheetRepository,
        ResponseHelper $responseHelper,
        MissionRepository $missionRepository
    ) {
        $this->timesheetRepository = $timesheetRepository;
        $this->responseHelper = $responseHelper;
        $this->missionRepository = $missionRepository;
    }

    /**
     * Get all timesheet entries
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $timeMissionEntries = $this->timesheetRepository
                ->getAllTimesheetEntries($request, config('constants.mission_type.TIME'));
            foreach ($timeMissionEntries as $value) {
                if ($value->missionLanguage) {
                    $value->setAttribute('title', $value->missionLanguage[0]->title);
                    unset($value->missionLanguage);
                }
                $value->setAppends([]);
            }

            $goalMissionEntries = $this->timesheetRepository
                ->getAllTimesheetEntries($request, config('constants.mission_type.GOAL'));
            foreach ($goalMissionEntries as $value) {
                if ($value->missionLanguage) {
                    $value->setAttribute('title', $value->missionLanguage[0]->title);
                    unset($value->missionLanguage);
                }
                $value->setAppends([]);
            }

            $timesheetEntries[config('constants.mission_type.TIME')] = $timeMissionEntries;
            $timesheetEntries[config('constants.mission_type.GOAL')] = $goalMissionEntries;

            $apiData = $timesheetEntries;
            $apiStatus = Response::HTTP_OK;
            $apiMessage = (count($timeMissionEntries->toArray()) > 0 && count($goalMissionEntries->toArray()) > 0) ?
            trans('messages.success.MESSAGE_TIMESHEET_ENTRIES_LISTING') :
            trans('messages.success.MESSAGE_NO_TIMESHEET_ENTRIES_FOUND');
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (PDOException $e) {
            return $this->PDO(
                config('constants.error_codes.ERROR_DATABASE_OPERATIONAL'),
                trans('messages.custom_error_message.ERROR_DATABASE_OPERATIONAL')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Store a newly timesheet into database
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse;
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $documentSizeLimit = config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT');
            $validator = Validator::make(
                $request->toArray(),
                [
                    'mission_id' => 'required|exists:mission,mission_id,deleted_at,NULL',
                    'date_volunteered' => 'required',
                    'day_volunteered' => ['required', Rule::in(config('constants.day_volunteered'))],
                    'documents.*' => 'max:' . $documentSizeLimit . '|valid_timesheet_document_type',
                ],
                [
                    'max' => 'Document size should not be max than ' .
                    (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024) . ' MB',
                ]
            );

            // If validator fails
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TIMESHEET_ITEMS_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            // Fetch mission type from missionid
            $missionData = $this->missionRepository->find($request->mission_id);
            try {
                // Fetch mission application data
                $missionApplicationData = $this->missionRepository->getMissionApplication(
                    $request->mission_id,
                    $request->auth->user_id
                );
            } catch (ModelNotFoundException $e) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_INVALID_DATA_FOR_TIMESHEET_ENTRY'),
                    trans('messages.custom_error_message.ERROR_INVALID_DATA_FOR_TIMESHEET_ENTRY')
                );
            }

            if ($missionApplicationData->approval_status
                != config('constants.timesheet_status.AUTOMATICALLY_APPROVED')) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.MISSION_APPLICATION_NOT_APPROVED'),
                    trans('messages.custom_error_message.MISSION_APPLICATION_NOT_APPROVED')
                );
            }

            $dateTime = ($request->date_volunteered != null) ?
            Carbon::createFromFormat('m-d-Y', $request->date_volunteered) : null;
            $dateTime = strtotime($dateTime);
            $date = date('Y-m-d', $dateTime);

            if ($missionData->mission_type == "GOAL") {
                $validator = Validator::make(
                    $request->all(),
                    [
                        "action" => "required|integer|min:1",
                    ]
                );

                // Remove extra params
                $request->request->remove('hours');
                $request->request->remove('minutes');

                // Fetch goal objective from goal mission
                $objective = $this->missionRepository->getGoalObjective($request->mission_id);

                // Fetch all added goal actions from database
                $totalAddedActions = $this->timesheetRepository->getAddedActions($request->mission_id);

                // Add total actions
                $totalActions = $totalAddedActions + $request->action;

                // Check total goals are not maximum than provided goals
                if ($totalActions > $objective->goal_objective) {
                    return $this->responseHelper->error(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                        config('constants.error_codes.ERROR_INVALID_ACTION'),
                        trans('messages.custom_error_message.ERROR_INVALID_ACTION')
                    );
                }
            } else {
                $validator = Validator::make(
                    $request->all(),
                    [
                        "hours" => "required|integer|between:0,23",
                        "minutes" => "required|integer|between:0,59",
                    ]
                );

                $time = $request->hours . ":" . $request->minutes;
                $request->request->add(['time' => $time]);
                // Remove extra params
                $request->request->remove('action');

                // Check start dates and end dates of mission
                if ($missionData->start_date) {
                    $startDate = (new Carbon($missionData->start_date))->format('Y-m-d');
                    if ($date < $startDate) {
                        return $this->responseHelper->error(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                            config('constants.error_codes.ERROR_MISSION_STARTDATE'),
                            trans('messages.custom_error_message.ERROR_MISSION_STARTDATE')
                        );
                    } else {
                        if ($missionData->end_date) {
                            $endDate = (new Carbon($missionData->end_date))->format('Y-m-d');
                            if ($date > $endDate) {
                                return $this->responseHelper->error(
                                    Response::HTTP_UNPROCESSABLE_ENTITY,
                                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                                    config('constants.error_codes.ERROR_MISSION_ENDDATE'),
                                    trans('messages.custom_error_message.ERROR_MISSION_ENDDATE')
                                );
                            }
                        }
                    }
                }

                // Fetch timesheet details by date and status
                $timesheetData = $this->timesheetRepository->getTimesheetDetailByDate(
                    $request->mission_id,
                    $date
                );

                // If timesheet status is approved for given date
                if ($timesheetData->count() > 0) {
                    return $this->responseHelper->error(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                        config('constants.error_codes.ERROR_TIMESHEET_ALREADY_DONE_FOR_DATE'),
                        trans('messages.custom_error_message.ERROR_TIMESHEET_ALREADY_DONE_FOR_DATE')
                    );
                }
            }

            // If validator fails
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TIMESHEET_ITEMS_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            // Remove params
            $request->request->remove('status_id');

            // Remove white space from notes
            if ($request->has('notes')) {
                $notes = trim($request->notes);
                $request->request->add(['notes' => $notes]);
            }

            // Store timesheet item
            $request->request->add(['user_id' => $request->auth->user_id]);
            $timesheet = $this->timesheetRepository->storeTimesheet($request);

            // Set response data
            $apiStatus = Response::HTTP_CREATED;
            $apiMessage = trans('messages.success.TIMESHEET_ENTRY_ADDED_SUCESSFULLY');
            $apiData = ['timesheet_id' => $timesheet->timesheet_id];

            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
        } catch (PDOException $e) {
            return $this->PDO(
                config('constants.error_codes.ERROR_DATABASE_OPERATIONAL'),
                trans(
                    'messages.custom_error_message.ERROR_DATABASE_OPERATIONAL'
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Update a timesheet
     *
     * @param \Illuminate\Http\Request $request
     * @param int $timesheetId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function update(Request $request, int $timesheetId): JsonResponse
    {
        try {
            $documentSizeLimit = config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT');
            $validator = Validator::make(
                $request->toArray(),
                [
                    'date_volunteered' => 'required',
                    'day_volunteered' => ['required', Rule::in(config('constants.day_volunteered'))],
                    'documents.*' => 'max:' . $documentSizeLimit . '|valid_timesheet_document_type',
                ],
                [
                    'max' => 'Document size should not be max than ' .
                    (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024) . ' MB',
                ]
            );

            // If validator fails
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TIMESHEET_ITEMS_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            // Fetch timesheet data
            $timesheetData = $this->timesheetRepository->getTimesheetData($timesheetId, $request->auth->user_id);
            $timesheetDetails = $timesheetData->toArray();

            // If timesheet status is approved
            if ($timesheetDetails["timesheet_status"]["status"] != config('constants.timesheet_status.PENDING')) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TIMESHEET_ALREADY_UPDATED'),
                    trans('messages.custom_error_message.ERROR_TIMESHEET_ALREADY_UPDATED')
                );
            }

            // Fetch mission type from missionid
            $missionData = $this->missionRepository->find($timesheetData->mission_id);
            if ($missionData->mission_type == "GOAL") {
                $validator = Validator::make(
                    $request->all(),
                    [
                        "action" => "required|integer|min:1",
                    ]
                );

                // Remove extra params
                $request->request->remove('hours');
                $request->request->remove('minutes');

                // Fetch goal objective from goal mission
                $objective = $this->missionRepository->getGoalObjective($timesheetData->mission_id);

                // Fetch all added actions from database
                $totalAddedActions = $this->timesheetRepository->getAddedActions($timesheetData->mission_id);

                // Add total actions
                $totalActions = ($totalAddedActions + $request->action) - $timesheetData->action;

                // Check total goals are not maximum than provided goals
                if ($totalActions > $objective->goal_objective) {
                    return $this->responseHelper->error(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                        config('constants.error_codes.ERROR_INVALID_ACTION'),
                        trans('messages.custom_error_message.ERROR_INVALID_ACTION')
                    );
                }
            } else {
                $validator = Validator::make(
                    $request->all(),
                    [
                        "hours" => "required|integer|between:0,23",
                        "minutes" => "required|integer|between:0,59",
                    ]
                );

                $time = $request->hours . ":" . $request->minutes;
                $request->request->add(['time' => $time]);

                // Remove extra params
                $request->request->remove('action');

                // Convert date in Y-m-d format
                $dateTime = ($request->date_volunteered != null) ?
                Carbon::createFromFormat('m-d-Y', $request->date_volunteered) : null;
                $dateTime = strtotime($dateTime);
                $date = date('Y-m-d', $dateTime);

                // Check start dates and end dates of mission
                if ($missionData->start_date) {
                    $startDate = (new Carbon($missionData->start_date))->format('Y-m-d');
                    if ($date < $startDate) {
                        return $this->responseHelper->error(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                            config('constants.error_codes.ERROR_MISSION_STARTDATE'),
                            trans('messages.custom_error_message.ERROR_MISSION_STARTDATE')
                        );
                    } else {
                        if ($missionData->end_date) {
                            $endDate = (new Carbon($missionData->end_date))->format('Y-m-d');
                            if ($date > $endDate) {
                                return $this->responseHelper->error(
                                    Response::HTTP_UNPROCESSABLE_ENTITY,
                                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                                    config('constants.error_codes.ERROR_MISSION_ENDDATE'),
                                    trans('messages.custom_error_message.ERROR_MISSION_ENDDATE')
                                );
                            }
                        }
                    }
                }
            }
            // If validator fails
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TIMESHEET_ITEMS_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            // Remove params
            $request->request->remove('mission_id');
            $request->request->remove('status_id');

            // Remove white space from notes
            if ($request->has('notes')) {
                $notes = trim($request->notes);
                $request->request->add(['notes' => $notes]);
            }

            // Store timesheet item
            $timesheet = $this->timesheetRepository->updateTimesheet($request, $timesheetId);

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.TIMESHEET_ENTRY_UPDATED_SUCESSFULLY');

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (PDOException $e) {
            return $this->PDO(
                config('constants.error_codes.ERROR_DATABASE_OPERATIONAL'),
                trans(
                    'messages.custom_error_message.ERROR_DATABASE_OPERATIONAL'
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->invalidArgument(
                config('constants.error_codes.ERROR_INVALID_ARGUMENT'),
                trans('messages.custom_error_message.ERROR_INVALID_ARGUMENT')
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.TIMESHEET_NOT_FOUND'),
                trans('messages.custom_error_message.TIMESHEET_NOT_FOUND')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Show timesheet data
     *
     * @param \Illuminate\Http\Request $request
     * @param int $timesheetId
     * @return \Illuminate\Http\JsonResponse;
     */
    public function show(Request $request, int $timesheetId): JsonResponse
    {
        try {
            // Fetch timesheet data
            $timesheetData = $this->timesheetRepository->getTimesheetData($timesheetId, $request->auth->user_id);
            $timesheetDetail = $timesheetData->toArray();
            if ($timesheetData->time != null) {
                $time = explode(":", $timesheetData->time);
                $timesheetDetail += ["hours" => $time[0]];
                $timesheetDetail += ["minutes" => $time[1]];
                unset($timesheetDetail["time"]);
            }

            $apiStatus = Response::HTTP_OK;
            $apiMessage = trans('messages.success.MESSAGE_TIMESHEET_LISTING');
            return $this->responseHelper->success($apiStatus, $apiMessage, $timesheetDetail);
        } catch (PDOException $e) {
            return $this->PDO(
                config('constants.error_codes.ERROR_DATABASE_OPERATIONAL'),
                trans(
                    'messages.custom_error_message.ERROR_DATABASE_OPERATIONAL'
                )
            );
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.TIMESHEET_NOT_FOUND'),
                trans('messages.custom_error_message.TIMESHEET_NOT_FOUND')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Remove the timesheet documents.
     *
     * @param \Illuminate\Http\Request $request
     * @param int  $timesheetId
     * @param int  $documentId
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $timesheetId, int $documentId): JsonResponse
    {
        try {
            // Fetch timesheet data
            $timesheetData = $this->timesheetRepository->getTimesheetData($timesheetId, $request->auth->user_id);

            // Delete timesheet document
            try {
                $timesheetDocument = $this->timesheetRepository->delete($documentId, $timesheetId);
            } catch (ModelNotFoundException $e) {
                return $this->modelNotFound(
                    config('constants.error_codes.TIMESHEET_DOCUMENT_NOT_FOUND'),
                    trans('messages.custom_error_message.TIMESHEET_DOCUMENT_NOT_FOUND')
                );
            }

            // Set response data
            $apiStatus = Response::HTTP_OK;
            $apiMessage = (!$timesheetDocument) ?
            trans('messages.success.MESSAGE_NO_RECORD_FOUND') :
            trans('messages.success.MESSAGE_TIMESHEET_DOCUMENT_DELETED');

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.TIMESHEET_NOT_FOUND'),
                trans('messages.custom_error_message.TIMESHEET_NOT_FOUND')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Submit timesheet
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse;
     */
    public function submitTimesheet(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->toArray(),
                [
                    'timesheet_entries' => 'required',
                    'timesheet_entries.*.timesheet_id' => 'required|exists:timesheet,timesheet_id,deleted_at,NULL',
                ]
            );

            // If validator fails
            if ($validator->fails()) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_TIMESHEET_ITEMS_REQUIRED_FIELDS_EMPTY'),
                    $validator->errors()->first()
                );
            }

            // Fetch timesheet data
            $timesheetData = $this->timesheetRepository->updateSubmittedTimesheet($request, $request->auth->user_id);

            $apiStatus = Response::HTTP_OK;
            $apiMessage = (!$timesheetData) ? trans('messages.success.TIMESHEET_ALREADY_SUBMITTED_FOR_APPROVAL') :
            trans('messages.success.TIMESHEET_SUBMITTED_SUCESSFULLY');

            return $this->responseHelper->success($apiStatus, $apiMessage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.TIMESHEET_NOT_FOUND'),
                trans('messages.custom_error_message.TIMESHEET_NOT_FOUND')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }

    /**
     * Fetch pending goal requests
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function goalRequestList(Request $request): JsonResponse
    {
        try {
            $goalRequestList = $this->timesheetRepository->goalRequestList($request);

            foreach ($goalRequestList as $value) {
                if ($value->missionLanguage) {
                    $value->setAttribute('title', $value->missionLanguage[0]->title);
                    unset($value->missionLanguage);
                }
                $value->setAppends([]);
            }

            $apiMessage = (count($goalRequestList) > 0) ?
            trans('messages.success.MESSAGE_GOAL_REQUEST_LISTING') :
            trans('messages.success.MESSAGE_NO_GOAL_REQUEST_FOUND');
            return $this->responseHelper->successWithPagination(Response::HTTP_OK, $apiMessage, $goalRequestList);
        } catch (PDOException $e) {
            return $this->PDO(
                config('constants.error_codes.ERROR_DATABASE_OPERATIONAL'),
                trans('messages.custom_error_message.ERROR_DATABASE_OPERATIONAL')
            );
        } catch (\Exception $e) {
            return $this->badRequest(trans('messages.custom_error_message.ERROR_OCCURRED'));
        }
    }
}
