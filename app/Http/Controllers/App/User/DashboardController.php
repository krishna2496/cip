<?php
namespace App\Http\Controllers\App\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Traits\RestExceptionHandlerTrait;
use App\Helpers\ResponseHelper;
use App\Helpers\Helpers;
use App\Repositories\User\UserRepository;
use App\Repositories\Timesheet\TimesheetRepository;
use App\Repositories\MissionApplication\MissionApplicationRepository;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Services\Dashboard\DashboardService;

//!  Dashboard controller
/*!
This controller is responsible for handling dashboard statistics listing operation.
 */
class DashboardController extends Controller
{
    use RestExceptionHandlerTrait;
    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Repositories\Timesheet\TimesheetRepository
     */
    private $timesheetRepository;
    
    /**
     * @var App\Repositories\MissionApplication\MissionApplicationRepository
     */
    private $missionApplicationRepository;

    /**
     * @var App\Repositories\TenantOption\TenantOptionRepository
     */
    private $tenantOptionRepository;
        
    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;
    
    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Services\Dashboard\DashboardService
     */
    private $dashboardService;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Repositories\Timesheet\TimesheetRepository $timesheetRepository
     * @param App\Repositories\MissionApplication\MissionApplicationRepository $missionApplicationRepository
     * @param App\Repositories\TenantOption\TenantOptionRepository $tenantOptionRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param App\Helpers\Helpers $helpers
     * @param App\Services\Dashboard\DashboardService $dashboardService
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        TimesheetRepository $timesheetRepository,
        MissionApplicationRepository $missionApplicationRepository,
        TenantOptionRepository $tenantOptionRepository,
        ResponseHelper $responseHelper,
        Helpers $helpers,
        DashboardService $dashboardService
    ) {
        $this->userRepository = $userRepository;
        $this->timesheetRepository = $timesheetRepository;
        $this->missionApplicationRepository = $missionApplicationRepository;
        $this->tenantOptionRepository = $tenantOptionRepository;
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->dashboardService = $dashboardService;
    }
    
    /**
     * Get dashboard statistics
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->auth->user_id;
        $year = ((!is_null($request->year)) && ($request->year != "")) ? $request->year : '';
        $month = ((!is_null($request->month)) && ($request->month != "")) ? $request->month : '';
        $missionId = $request->mission_id ?? null;
        $totalHours = $totalGoals = 0;
        $currentYear = ($year != '') ? $year : (int) date('Y');

        $timesheetData = $this->timesheetRepository->getTotalHours($userId, $year, $month);
        $pendingApplicationCount = $this->missionApplicationRepository->pendingApplicationCount($userId, $year, $month);
        $approvedApplicationCount = $this->missionApplicationRepository->missionApplicationCount(
            $userId,
            $year,
            $month
        );
        $organizationCount = $this->missionApplicationRepository->organizationCount($userId, $year, $month);
        $goalHours = $this->userRepository->getUserHoursGoal($userId);
        $tenantGoalHours = $this->tenantOptionRepository->getOptionValueFromOptionName('default_user_hours_goal');
        $tenantGoalHours = $tenantGoalHours->option_value ?? config('constants.DEFAULT_USER_HOURS_GOAL');
        $allUsersTimesheetData = $this->timesheetRepository->getUsersTotalHours($year, $month);
        $totalGoalHours = $this->timesheetRepository->getTotalHoursForYear($userId, $year);
        // For dashboard chart : Hours per month
        $chartData = $this->timesheetRepository->getTotalHoursbyMonth($userId, $currentYear, $missionId);
        // For total hours
        foreach ($timesheetData as $timesheet) {
            $totalHours += $timesheet['total_minutes'];
        }
        
        // For hours tracked this year
        foreach ($totalGoalHours as $timesheetHours) {
            $totalGoals += $timesheetHours['total_minutes'];
        }

        // For volunteering Rank
        $volunteeringRank = $this->dashboardService->getvolunteeringRank($allUsersTimesheetData, $userId);
        
        $apiData['total_hours'] = $this->helpers->convertInReportTimeFormat($totalHours);
        // dd($apiData);
        $apiData['volunteering_rank'] = (int)$volunteeringRank;
        $apiData['open_volunteering_requests'] = $pendingApplicationCount;
        $apiData['mission_count'] = $approvedApplicationCount;
        $apiData['voted_missions'] = '';
        $apiData['organization_count'] = count($organizationCount);
        $apiData['total_goal_hours'] = (!is_null($goalHours)) ? $goalHours : $tenantGoalHours;
        $apiData['completed_goal_hours'] = (int)($totalGoals / 60);
        $apiData['chart'] = $chartData;
        
        // Set response data
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_DASHBOARD_STATISTICS_LISTING');
        return $this->responseHelper->success(Response::HTTP_OK, $apiMessage, $apiData);
    }
}
