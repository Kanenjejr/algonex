<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\HrContoller;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\ManfctrContoller;
use App\Http\Controllers\CrmSplyContoller;
use App\Http\Controllers\LogisticsController;
use App\Http\Controllers\MicrofinanceController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DrillingBlastingController;
use App\Http\Controllers\CampNewsController;
use App\Http\Controllers\IctController;
use App\Http\Controllers\SalesManagementController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosSalesManagementController;
use App\Http\Controllers\PurchaseManagementController;
use App\Http\Controllers\ProformaController;
use App\Http\Controllers\SalesInvoiceController;

            Route::get('/', [AuthController::class, 'login'])->name('login');
            Route::get('/login', function (){return view('auth.login');})->name('login');
            Route::match(['get', 'post'], '/auth', [AuthController::class, 'auth'])->name('auth');
            Route::middleware(['auth'])->group(function () {
            Route::get('/profile', [AuthController::class, 'profile'])->name('profile')->middleware('auth');
            Route::get('/profile/edit', [AuthController::class, 'editProfile'])->name('profile.edit');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
            Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
            Route::group(['middleware' => 'prevent-back-history'], function () {
            Route::get('/main', [AuthController::class, 'main'])->name('main')->middleware('auth');
            Route::get('/change-password', [AuthController::class, 'changePassword'])->name('change-password')->middleware('auth');
            Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('password.update')->middleware('auth');
            // Dashboard
            Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard')->middleware('auth');
            Route::get('/company-dashboard', [CompanyDashboardController::class, 'index'])->name('company.dashboard')->middleware('auth');
            Route::get('/company/{company}/dashboard', [CompanyDashboardController::class, 'departments'])->name('company.departments')->middleware('auth');
            // Administration
            Route::get('/business-admin', [AdminController::class, 'businessAdmin']) ->name('business-admin')->middleware('can:Finance-Administration-Modules');
            // Company sites
            Route::get('/admin/company-sites', [AdminController::class, 'companyIndex'])->name('company.index')->middleware('can:View-Company-Site');
            Route::post('/admin/company-sites', [AdminController::class, 'companyStore'])->name('company.store')->middleware('can:Register-Company-Site');
            Route::put('/admin/company-sites/{id}', [AdminController::class, 'companyUpdate'])->name('company.update')->middleware('can:Edit-Company-Site');
            Route::get('/admin/company-sites/remove/{id}', [AdminController::class, 'companyDelete'])->name('company.delete')->middleware('can:Delete-Company-Site');
            // Company units (CRUD)
            Route::get('/admin/company-units', [AdminController::class, 'companyUnitIndex'])->name('companyunit.index')->middleware('can:View-Company-Unit');
            Route::post('/admin/company-units', [AdminController::class, 'companyUnitStore'])->name('companyunit.store')->middleware('can:Register-Company-Unit');
            Route::put('/admin/company-units/{id}', [AdminController::class, 'companyUnitUpdate'])->name('companyunit.update')->middleware('can:Edit-Company-Unit');
            Route::get('/admin/company-units/remove/{id}', [AdminController::class, 'companyUnitDelete'])->name('companyunit.delete')->middleware('can:Delete-Company-Unit');
            // Work points
            Route::get('/admin/work-points', [AdminController::class, 'workPointIndex'])->name('workpoint.index')->middleware('can:View-WorkPoint');
            Route::post('/admin/work-points', [AdminController::class, 'workPointStore'])->name('workpoint.store')->middleware('can:Register-WorkPoint');
            Route::put('/admin/work-points/{id}', [AdminController::class, 'workPointUpdate'])->name('workpoint.update')->middleware('can:Edit-WorkPoint');
            Route::get('/admin/work-points/remove/{id}', [AdminController::class, 'workPointDelete'])->name('workpoint.delete')->middleware('can:Delete-WorkPoint');
            // Departments
            Route::get('/admin/departments', [AdminController::class, 'deptinfo'])->name('departments.index')->middleware('can:View-Department');
            Route::post('/admin/departments', [AdminController::class, 'regdeptinfo'])->name('departments.store')->middleware('can:Register-Department');
            Route::put('/admin/departments/{id}', [AdminController::class, 'updatedeptinfo'])->name('departments.update')->middleware('can:Edit-Department');
            Route::get('/admin/departments/remove/{id}', [AdminController::class, 'remvdeptinfo'])->name('departments.remove')->middleware('can:Delete-Department');
            // Sections
            Route::get('/admin/sections', [AdminController::class, 'sectinfo'])->name('sections.index')->middleware('can:View-Section');
            Route::post('/admin/sections', [AdminController::class, 'regsectinfo'])->name('sections.store')->middleware('can:Register-Section');
            Route::put('/admin/sections/{id}', [AdminController::class, 'updatesectinfo'])->name('sections.update')->middleware('can:Edit-Section');
            Route::get('/admin/sections/remove/{id}', [AdminController::class, 'remvsectinfo'])->name('sections.remove')->middleware('can:Delete-Section');
            //roles and permission routes
            Route::get('/roleinfo', [PermissionController::class, 'roleinfo'])->name('roleinfo')->middleware('can:View-Title/Role-Details');
            Route::post('/regrole', [PermissionController::class, 'regrole'])->name('regrole')->middleware('can:Register-Title/Role-Details');
            Route::get('/remvrole{id}', [PermissionController::class, 'remvrole'])->name('remvrole')->middleware('can:Delete-Title/Role-Details');
            //assign roles
            Route::post('/storerole', [PermissionController::class, 'storerole'])->name('storerole');
            Route::get('/assignrole', [PermissionController::class, 'assignrole'])->name('assignrole');
            Route::get('/attachrole{id}', [PermissionController::class, 'attachrole'])->name('attachrole');
            // HR
            Route::get('/hr', [HrContoller::class, 'hr'])->name('hr')->middleware('can:HR-Modules');
            Route::get('/staff', [HrContoller::class, 'index'])->name('staff.index')->middleware('can:View-Staff');
            Route::post('/staff', [HrContoller::class, 'store'])->name('staff.store')->middleware('can:Register-Staff');
            Route::put('/staff/{id}', [HrContoller::class, 'update'])->name('staff.update')->middleware('can:Edit-Staff');
            // ICT
            Route::prefix('ict')->name('ict.')->middleware(['auth', 'can:View-ICT-Menu'])->group(function () {
                // Issues routes
            Route::get('/issues', [IctController::class, 'issuesIndex'])->name('issues.index')->middleware('can:View-Software-Hardware-Issues');
            Route::post('/issues', [IctController::class, 'storeIssue'])->name('issues.store')->middleware('can:Register-Software-Hardware-Issues');
            Route::put('/issues/{id}', [IctController::class, 'updateIssue'])->name('issues.update')->middleware('can:Edit-Software-Hardware-Issues');
            Route::delete('/issues/{id}', [IctController::class, 'destroyIssue'])->name('issues.destroy')->middleware('can:Delete-Software-Hardware-Issues');
            // Maintenance routes
            Route::get('/maintenance', [IctController::class, 'maintenanceIndex'])->name('maintenance.index')->middleware('can:View-IT-Maintenance');
            Route::post('/maintenance', [IctController::class, 'storeMaintenance'])->name('maintenance.store')->middleware('can:Register-IT-Maintenance');
            Route::put('/maintenance/{id}', [IctController::class, 'updateMaintenance'])->name('maintenance.update')->middleware('can:Edit-IT-Maintenance');
            Route::delete('/maintenance/{id}', [IctController::class, 'destroyMaintenance'])->name('maintenance.destroy')->middleware('can:Delete-IT-Maintenance');});
            //HR
            Route::get('/staff/remove/{id}', [HrContoller::class, 'remove'])->name('staff.remove')->middleware('can:Delete-Staff');
            // Activate / Deactivate
            Route::get('/staff/activate/{id}', [HrContoller::class, 'activate'])->name('staff.activate')->middleware('can:Edit-Staff');
            Route::get('/staff/deactivate/{id}', [HrContoller::class, 'deactivate'])->name('staff.deactivate')->middleware('can:Edit-Staff');
            // Reset password (set to default password)
            Route::get('/staff/reset-password/{id}', [HrContoller::class, 'resetPassword'])->name('staff.reset.password')->middleware('can:Edit-Staff');
            // Next of Kins
            Route::get('/admin/hr/next-of-kins', [HrContoller::class, 'staffNextOfKins'])->name('hr.nextofkins.index')->middleware('can:View-NextOfKin');
            Route::post('/admin/hr/next-of-kins', [HrContoller::class, 'storeNextOfKin'])->name('hr.nextofkins.store')->middleware('can:Register-NextOfKin');
            Route::put('/admin/hr/next-of-kins/{id}', [HrContoller::class, 'updateNextOfKin'])->name('hr.nextofkins.update')->middleware('can:Edit-NextOfKin');
            Route::get('/admin/hr/next-of-kins/remove/{id}', [HrContoller::class, 'removeNextOfKin'])->name('hr.nextofkins.remove')->middleware('can:Delete-NextOfKin');
            // Educations
            Route::get('/admin/hr/educations', [HrContoller::class, 'staffEducations'])->name('hr.educations.index')->middleware('can:View-Education');
            Route::post('/admin/hr/educations', [HrContoller::class, 'storeEducation'])->name('hr.educations.store')->middleware('can:Register-Education');
            Route::put('/admin/hr/educations/{id}', [HrContoller::class, 'updateEducation'])->name('hr.educations.update')->middleware('can:Edit-Education');
            Route::get('/admin/hr/educations/remove/{id}', [HrContoller::class, 'removeEducation'])->name('hr.educations.remove')->middleware('can:Delete-Education');
            // Documents
            Route::get('/admin/hr/documents', [HrContoller::class, 'staffDocuments'])->name('hr.documents.index')->middleware('can:View-Staff-Documents');
            Route::post('/admin/hr/documents', [HrContoller::class, 'storeDocument'])->name('hr.documents.store')->middleware('can:Register-Staff-Documents');
            Route::put('/admin/hr/documents/{id}', [HrContoller::class, 'updateDocument'])->name('hr.documents.update')->middleware('can:Edit-Staff-Documents');
            Route::get('/admin/hr/documents/remove/{id}', [HrContoller::class, 'removeDocument'])->name('hr.documents.remove')->middleware('can:Delete-Staff-Documents');
            // Leaves
            Route::get('/admin/hr/leaves', [HrContoller::class, 'leaves'])->name('hr.leaves.index')->middleware('can:View-Leaves');
            Route::post('/admin/hr/leaves', [HrContoller::class, 'storeLeave'])->name('hr.leaves.store')->middleware('can:Register-Leave');
            Route::put('/admin/hr/leaves/{id}', [HrContoller::class, 'updateLeave'])->name('hr.leaves.update')->middleware('can:Edit-Leave');
            Route::get('/admin/hr/leaves/remove/{id}', [HrContoller::class, 'removeLeave'])->name('hr.leaves.remove')->middleware('can:Delete-Leave');
            // Approve and Print
            Route::post('/admin/hr/leaves/{id}/approve', [HrContoller::class, 'approveLeave'])->name('hr.leaves.approve')->middleware('can:Approve-Leave');
            Route::get('/admin/hr/leaves/{id}/print', [HrContoller::class, 'printLeave'])->name('hr.leaves.print')->middleware('can:View-Leaves');
            // Loans/Advances
            Route::get('/admin/hr/loans', [HrContoller::class, 'employeeLoans'])->name('hr.loans.index')->middleware('can:View-Loans');
            Route::post('/admin/hr/loans', [HrContoller::class, 'storeLoan'])->name('hr.loans.store')->middleware('can:Register-Loan');
            Route::put('/admin/hr/loans/{id}', [HrContoller::class, 'updateLoan'])->name('hr.loans.update')->middleware('can:Edit-Loan');
            Route::get('/admin/hr/loans/remove/{id}', [HrContoller::class, 'removeLoan'])->name('hr.loans.remove')->middleware('can:Delete-Loan');
            // Payroll
            Route::get('/admin/hr/payrolls', [HrContoller::class,'payrolls'])->name('hr.payrolls.index')->middleware('can:View-Payrolls');
            Route::post('/admin/hr/payrolls/prepare', [HrContoller::class,'preparePayroll'])->name('hr.payrolls.prepare')->middleware('can:Register-Payroll');
            Route::post('/admin/hr/payrolls/{id}/approve', [HrContoller::class,'approvePayroll'])->name('hr.payrolls.approve')->middleware('can:Approve-Payroll');
            Route::post('/admin/hr/payrolls/{id}/pay', [HrContoller::class,'payPayroll'])->name('hr.payrolls.pay')->middleware('can:Pay-Payroll');
            Route::get('/admin/hr/payrolls/remove/{id}', [HrContoller::class,'removePayroll'])->name('hr.payrolls.remove')->middleware('can:Delete-Payroll');
            // payroll details & editable lines
            Route::get('/admin/hr/payrolls/{id}', [HrContoller::class,'showPayroll'])->name('hr.payrolls.show')->middleware('can:View-Payrolls');
            Route::put('/admin/hr/payrolls/{payroll}/lines/{line}', [HrContoller::class,'updatePayrollLine'])->name('hr.payrolls.line.update')->middleware('can:Edit-Payroll');
            // printable sheets & reports
            Route::get('/admin/hr/payrolls/{id}/sheet/net', [HrContoller::class,'payrollSheetNet'])->name('hr.payrolls.sheet.net')->middleware('can:Print-Payroll-Sheets');
            Route::get('/admin/hr/payrolls/{id}/sheet/nssf', [HrContoller::class,'payrollSheetNssf'])->name('hr.payrolls.sheet.nssf')->middleware('can:Print-Payroll-Sheets');
            Route::get('/admin/hr/payrolls/{id}/sheet/wcf', [HrContoller::class,'payrollSheetWcf'])->name('hr.payrolls.sheet.wcf')->middleware('can:Print-Payroll-Sheets');
            Route::get('/admin/hr/payrolls/{id}/sheet/sdl', [HrContoller::class,'payrollSheetSdl'])->name('hr.payrolls.sheet.sdl')->middleware('can:Print-Payroll-Sheets');
            Route::get('/admin/hr/payrolls/{id}/sheet/loans', [HrContoller::class,'payrollSheetLoans'])->name('hr.payrolls.sheet.loans')->middleware('can:Print-Payroll-Sheets');
            Route::get('/admin/hr/payrolls/{id}/sheet/heslb', [HrContoller::class, 'payrollSheetHeslb'])->name('hr.payrolls.sheet.heslb')->middleware('can:Print-Payroll-Sheets');
         // reports (one endpoint showing links to sheets)
            Route::get('/admin/hr/payrolls/{id}/reports', [HrContoller::class,'payrollReports'])->name('hr.payrolls.reports')->middleware('can:View-Payroll-Reports');
            // Payslip view + print
            Route::get('/admin/hr/payrolls/{payrollId}/slip/{userId}', [HrContoller::class, 'payslip'])->name('hr.payrolls.slip')->middleware('can:View-Payslip');
            Route::get('/admin/hr/payrolls/{payrollId}/slip/{userId}/print', [HrContoller::class, 'printPayslip'])->name('hr.payrolls.slip.print')->middleware('can:Print-Payslip');
            // Overtime
            Route::get('/admin/hr/overtimes', [HrContoller::class,'overtimes'])->name('hr.overtimes.index')->middleware('can:View-Overtimes');
            Route::post('/admin/hr/overtimes', [HrContoller::class,'storeOvertime'])->name('hr.overtimes.store')->middleware('can:Register-Overtime');
            Route::put('/admin/hr/overtimes/{id}', [HrContoller::class,'updateOvertime'])->name('hr.overtimes.update')->middleware('can:Edit-Overtime'); // <-- ADD THIS
            Route::post('/admin/hr/overtimes/{id}/approve', [HrContoller::class,'approveOvertime'])->name('hr.overtimes.approve')->middleware('can:Approve-Overtime');
            Route::post('/admin/hr/overtimes/{id}/pay', [HrContoller::class,'payOvertime'])->name('hr.overtimes.pay')->middleware('can:Pay-Overtime');
            Route::get('/admin/hr/overtimes/remove/{id}', [HrContoller::class,'removeOvertime'])->name('hr.overtimes.remove')->middleware('can:Delete-Overtime');
            // Absences
            Route::get('/admin/hr/absences', [HrContoller::class, 'absences'])->name('hr.absences.index')->middleware('can:View-Absences');
            Route::post('/admin/hr/absences', [HrContoller::class, 'storeAbsence'])->name('hr.absences.store')->middleware('can:Register-Absence');
            Route::put('/admin/hr/absences/{id}', [HrContoller::class, 'updateAbsence'])->name('hr.absences.update')->middleware('can:Edit-Absence');
            Route::get('/admin/hr/absences/remove/{id}', [HrContoller::class, 'removeAbsence'])->name('hr.absences.remove')->middleware('can:Delete-Absence');
            Route::post('/admin/hr/absences/{id}/approve', [HrContoller::class, 'approveAbsence'])->name('hr.absences.approve')->middleware('can:Approve-Absence');
            // HESLB
            Route::get('/admin/hr/heslb', [HrContoller::class,'heslbLoans'])->name('hr.heslb.index')->middleware('can:View-Loans');
            Route::post('/admin/hr/heslb', [HrContoller::class,'storeHeslbLoan'])->name('hr.heslb.store')->middleware('can:Register-Loan');
            Route::put('/admin/hr/heslb/{id}', [HrContoller::class,'updateHeslbLoan'])->name('hr.heslb.update')->middleware('can:Edit-Loan');
            Route::get('/admin/hr/heslb/remove/{id}', [HrContoller::class,'removeHeslbLoan'])->name('hr.heslb.remove')->middleware('can:Delete-Loan');
            // Allowance / Bonus
            Route::get('/admin/hr/salary-adjustments', [HrContoller::class,'salaryAdjustments'])->name('hr.salary-adjustments.index')->middleware('can:View-Payrolls');
            Route::post('/admin/hr/salary-adjustments', [HrContoller::class,'storeSalaryAdjustment'])->name('hr.salary-adjustments.store')->middleware('can:Register-Payroll');
            Route::put('/admin/hr/salary-adjustments/{id}', [HrContoller::class,'updateSalaryAdjustment'])->name('hr.salary-adjustments.update')->middleware('can:Edit-Payroll');
            Route::get('/admin/hr/salary-adjustments/remove/{id}', [HrContoller::class,'removeSalaryAdjustment'])->name('hr.salary-adjustments.remove')->middleware('can:Delete-Payroll');
            // Payroll rollback
            Route::post('/admin/hr/payrolls/{id}/rollback', [HrContoller::class,'rollbackPayroll'])->name('hr.payrolls.rollback')->middleware('can:Delete-Payroll');
            // Accounting
            Route::get('/accounting', [AccountController::class, 'accounting'])->name('accounting')->middleware('can:Accounting-Modules');
            //accounting code routes
            Route::get('/admin/accnt-charts', [AccountController::class, 'chartinfo'])->name('accntcharts.index')->middleware('can:View-Accounting-Code');
            Route::post('/admin/accnt-charts', [AccountController::class, 'storechart'])->name('accntcharts.store')->middleware('can:Register-Accounting-Code');
            Route::put('/admin/accnt-charts/{id}', [AccountController::class, 'updatechart'])->name('accntcharts.update')->middleware('can:Edit-Accounting-Code');
            Route::get('/admin/accnt-charts/remove/{id}', [AccountController::class, 'removechart'])->name('accntcharts.remove')->middleware('can:Delete-Accounting-Code');
            // new: subcharts
            Route::get('/admin/accnt-subcharts', [AccountController::class, 'subchartIndex'])->name('accntsubcharts.index')->middleware('can:View-Sub-Accounting-Code');
            Route::post('/admin/accnt-subcharts', [AccountController::class, 'storeSubchart'])->name('accntsubcharts.store')->middleware('can:Register-Sub-Accounting-Code');
            Route::put('/admin/accnt-subcharts/{id}', [AccountController::class, 'updateSubchart'])->name('accntsubcharts.update')->middleware('can:Edit-Sub-Accounting-Code');
            Route::get('/admin/accnt-subcharts/remove/{id}', [AccountController::class, 'removeSubchart'])->name('accntsubcharts.remove')->middleware('can:Delete-Sub-Accounting-Code');
            //accounting trasaction
            Route::get('/admin/accnt-transactions', [AccountController::class, 'accntrans'])->name('accnttransactions.index')->middleware('can:View-Accounting-Transactions');
            Route::post('/admin/accnt-transactions', [AccountController::class, 'storeaccntrans'])->name('accnttransactions.store')->middleware('can:Register-Accounting-Transactions');
            Route::put('/admin/accnt-transactions/{id}', [AccountController::class, 'updateaccntrans'])->name('accnttransactions.update')->middleware('can:Edit-Accounting-Transactions');
            Route::get('/admin/accnt-transactions/remove/{id}', [AccountController::class, 'removeaccntrans'])->name('accnttransactions.remove')->middleware('can:Delete-Accounting-Transactions');
            Route::post('/admin/accnt-transactions/import', [AccountController::class, 'importaccntrans'])->name('accnttransactions.import')->middleware('can:Import-Accounting-Transactions');
            Route::post('/admin/accnt-transactions/verify/{id}', [AccountController::class, 'verifyaccntrans'])->name('accnttransactions.verify')->middleware('can:Verify-Accounting-Transactions');
            Route::post('/admin/accnt-transactions/approve/{id}', [AccountController::class, 'approveaccntrans'])->name('accnttransactions.approve')->middleware('can:Approve-Accounting-Transactions');

            Route::get('/admin/accnt-transactions/report', [AccountController::class, 'reportaccntrans'])->name('accnttransactions.report')->middleware('can:View-Accounting-Reports');
            //Accounting reports
            Route::get('/admin/reports/ledger/{year}', [AccountController::class, 'ledgerReport'])->name('ledger')->middleware('can:View-Ledger-Details-Report');
            Route::get('/admin/reports/ledger/{year}/details/{accountId}', [AccountController::class, 'ledgerDetailReport'])->name('ledger.details')->middleware('can:View-Ledger-Details-Report');
            Route::get('/admin/reports/trial-balance/{year}', [AccountController::class, 'trialBalanceReport'])->name('trialbalance')->middleware('can:View-Trial-Balance-Report');
            Route::get('/admin/reports/monthly-trial-balance/{year}', [AccountController::class, 'monthlyTrialBalanceReport'])->name('mnttrialbalance')->middleware('can:View-Monthly-Trial-Balance-Report');
            Route::get('/admin/reports/profit-loss/{year}', [AccountController::class, 'profitLossReport'])->name('profitloss')->middleware('can:View-Profit-&-Loss-Report');
            Route::get('/admin/reports/balance-sheet/{year}', [AccountController::class, 'balanceSheetReport'])->name('balancesheet')->middleware('can:View-Balance-Sheet-Report');
            Route::get('/admin/reports/change-in-equity/{year}', [AccountController::class, 'changeInEquityReport'])->name('changeinequity')->middleware('can:View-Change-in-Equity-Report');
            Route::get('/admin/reports/cash-flow/{year}', [AccountController::class, 'cashFlowReport'])->name('cashflow')->middleware('can:View-Cash-Flow-Report');
            // Combined Financial Statements: uses existing accounting balances + existing asset report as Note 7 source.
            Route::get('/admin/reports/financial-statements/{year}', [AccountController::class, 'financialStatementsReport'])->name('financialstatements')->middleware('can:View-Accounting-Reports');
            //Assets reports
            Route::get('/admin/assets/report', [AssetsController::class, 'assertreport'])->name('assets.report')->middleware('can:View-Asset-Report');
            Route::post('/admin/assets/report', [AssetsController::class, 'assertreport'])->name('assets.report.search')->middleware('can:View-Asset-Report');
            // Asset register import from Excel / CSV.
            Route::get('/admin/assets/import', [AssetsController::class, 'assetImportForm'])->name('assets.import.form')->middleware('can:View-Asset-Report');
            Route::post('/admin/assets/import', [AssetsController::class, 'assetImportExcel'])->name('assets.import.excel')->middleware('can:View-Asset-Report');
            // Asset categories
            Route::get('/admin/assets/categories', [AssetsController::class, 'categoriesIndex'])->name('assets.categories')->middleware('can:View-Asset-Categories');
            Route::post('/admin/assets/categories', [AssetsController::class, 'storeCategory'])->name('assets.categories.store')->middleware('can:Register-Asset-Categories');
            Route::put('/admin/assets/categories/{id}', [AssetsController::class, 'updateCategory'])->name('assets.categories.update')->middleware('can:Edit-Asset-Categories');
            Route::get('/admin/assets/categories/remove/{id}', [AssetsController::class, 'removeCategory'])->name('assets.categories.remove')->middleware('can:Delete-Asset-Categories');
            // Asset transactions
            Route::get('/admin/assets', [AssetsController::class, 'assetsIndex'])->name('assets.index')->middleware('can:View-Asset-Transactions');
            Route::post('/admin/assets', [AssetsController::class, 'storeAsset'])->name('assets.store')->middleware('can:Register-Asset-Transactions');
            Route::put('/admin/assets/{id}', [AssetsController::class, 'updateAsset'])->name('assets.update')->middleware('can:Edit-Asset-Transactions');
            Route::get('/admin/assets/remove/{id}', [AssetsController::class, 'removeAsset'])->name('assets.remove')->middleware('can:Delete-Asset-Transactions');
            // disposal & revalue
            Route::post('/admin/assets/{id}/dispose', [AssetsController::class, 'disposeAsset'])->name('assets.dispose')->middleware('can:Dispose-Asset');
            Route::post('/admin/assets/{id}/revalue', [AssetsController::class, 'revalueAsset'])->name('assets.revalue')->middleware('can:Revalue-Asset');
           //news routes
            Route::get('/admin/news', [CampNewsController::class, 'index'])->name('news.index')->middleware('can:View-News');
            Route::post('/admin/news', [CampNewsController::class, 'store'])->name('news.store')->middleware('can:Register-News');
            Route::put('/admin/news/{id}', [CampNewsController::class, 'update'])->name('news.update')->middleware('can:Edit-News');
            Route::get('/admin/news/remove/{id}', [CampNewsController::class, 'destroy'])->name('news.remove')->middleware('can:Delete-News');
            //sales management
            Route::prefix('sales-management')->group(function () {
            Route::get('/', [SalesManagementController::class, 'dashboard'])->name('sales.management.dashboard');
            Route::get('/customers', [SalesManagementController::class, 'customers'])->name('sales.customers')->middleware('can:View-Customer-Contacts');
            Route::get('/contacts', [SalesManagementController::class, 'contacts'])->name('sales.contacts')->middleware('can:View-Customer-Contacts');
            Route::get('/campaigns', [SalesManagementController::class, 'campaigns'])->name('sales.campaigns')->middleware('can:View-Campaigns');
            Route::get('/reports', [SalesManagementController::class, 'reports'])->name('sales.reports')->middleware('can:View-Sales-Reports');

            // CAMPAIGNS
            Route::get('/admin/sales/campaigns',[SalesManagementController::class, 'campaigns'])->name('sales.campaigns.index')->middleware('can:View-Campaigns');
            Route::post('/campaigns/store',[SalesManagementController::class, 'storeCampaign'])->name('campaigns.store')->middleware('can:Create-Campaign');
            Route::get('/campaigns/edit/{id}',[SalesManagementController::class, 'editCampaign'])->name('campaigns.edit')->middleware('can:Edit-Campaigns');
            Route::post('/campaigns/update/{id}',[SalesManagementController::class, 'updateCampaign'])->name('campaigns.update')->middleware('can:Edit-Campaigns');
            Route::delete('/campaigns/delete/{id}',[SalesManagementController::class, 'deleteCampaign'])->name('campaigns.delete')->middleware('can:Delete-Campaigns');
            Route::get('/responses', [SalesManagementController::class, 'responses'])->name('sales.responses');
            
            Route::get('/opportunities',[SalesManagementController::class, 'opportunities'])->name('sales.opportunities')->middleware('can:View-Opportunities');
            Route::post('/opportunities/store',[SalesManagementController::class, 'storeOpportunity'])->name('opportunities.store')->middleware('can:Register-Opportunities');
            Route::get('/opportunities/edit/{id}',[SalesManagementController::class, 'editOpportunity'])->name('opportunities.edit')->middleware('can:Edit-Opportunities');
            Route::post('/opportunities/update/{id}',[SalesManagementController::class, 'updateOpportunity'])->name('opportunities.update')->middleware('can:Edit-Opportunities');
            Route::delete('/opportunities/delete/{id}',[SalesManagementController::class, 'deleteOpportunity'])->name('opportunities.delete')->middleware('can:Delete-Opportunities');
            Route::get('/activities',[SalesManagementController::class, 'activities'])->name('sales.activities');
            Route::post('/sales-management/activities/store',[SalesManagementController::class, 'storeActivity'])->name('activities.store');
            Route::get('/activities/edit/{id}',[SalesManagementController::class, 'editActivity'])->name('activities.edit')->middleware('can:Edit-Activities');
            Route::post('/activities/update/{id}',[SalesManagementController::class, 'updateActivity'])->name('activities.update')->middleware('can:Edit-Activities');
            Route::delete('/activities/delete/{id}',[SalesManagementController::class, 'deleteActivity'])->name('activities.delete')->middleware('can:Delete-Activities');
            Route::post('/sales/payments/store', [SalesManagementController::class, 'storePayment'])->name('sales.payments.store')->middleware('can:Create-Payment');
            Route::get('/admin/get-business-units/{company_id}', function ($company_id) {return \App\Models\Company_unit::where('company_id', $company_id)->get();});
            Route::get('/admin/get-workpoints/{unit_id}', function ($unit_id) {return \App\Models\WorkPoint::where('comp_unit_id', $unit_id)->get();});
            Route::get('/pos-sales', [SalesManagementController::class, 'pos'])->name('sales.pos');});
            Route::get('/credit-notes', [SalesManagementController::class, 'creditNotes'])->name('sales.credit-notes')->middleware('permission:View-Credit-Notes');
            Route::post('/sales/invoice', [SalesManagementController::class, 'createInvoice'])->name('sales.invoice');
            Route::get('/stock-ledger', [SalesManagementController::class,'stockLedger'])->middleware('permission:View-Stock-Ledger')->name('stock.ledger');
            Route::get('/stock/movement',[StockManagementController::class,'stockMovementPage'])->middleware('permission:View-Stock-Movement')->name('stock.movement');
            Route::get('/store-requests', [SalesManagementController::class,'storeRequests'])->middleware('permission:View-Store-Requests')->name('store.requests');
            Route::get('/stock-export-excel', [SalesManagementController::class,'exportStockExcel'])->middleware('permission:View-Stock-Ledger')->name('stock.export.excel');
            Route::get('/stock-export-pdf', [SalesManagementController::class,'exportStockPdf'])->middleware('permission:View-Stock-Ledger')->name('stock.export.pdf');
            // SALES / STORE
            Route::get('/admin/store/general-supply/items', [SalesController::class, 'gsItemsIndex'])->name('sales.gs.items.index')->middleware('can:View-Item-Details');
            Route::post('/admin/store/general-supply/items', [SalesController::class, 'gsItemsStore'])->name('sales.gs.items.store')->middleware('can:Register-Item-Details');
            Route::put('/admin/store/general-supply/items/{id}', [SalesController::class, 'gsItemsUpdate'])->name('sales.gs.items.update')->middleware('can:Edit-Item-Details');
            Route::get('/admin/store/general-supply/items/remove/{id}', [SalesController::class, 'gsItemsDestroy'])->name('sales.gs.items.remove')->middleware('can:Delete-Item-Details');

            Route::get('/admin/store/general-supply/descriptions', [SalesController::class, 'gsDescriptionsIndex'])->name('sales.gs.descriptions.index')->middleware('can:View-Item-Description-Details');
            Route::post('/admin/store/general-supply/descriptions', [SalesController::class, 'gsDescriptionsStore'])->name('sales.gs.descriptions.store')->middleware('can:Register-Item-Description-Details');
            Route::put('/admin/store/general-supply/descriptions/{id}', [SalesController::class, 'gsDescriptionsUpdate'])->name('sales.gs.descriptions.update')->middleware('can:Edit-Item-Description-Details');
            Route::get('/admin/store/general-supply/descriptions/remove/{id}', [SalesController::class, 'gsDescriptionsDestroy'])->name('sales.gs.descriptions.remove')->middleware('can:Delete-Item-Description-Details');

            Route::get('/admin/store/general-supply/received', [SalesController::class, 'gsReceivedIndex'])->name('sales.gs.received.index')->middleware('can:View-Received-Item-Details');
            Route::post('/admin/store/general-supply/received', [SalesController::class, 'gsReceivedStore'])->name('sales.gs.received.store')->middleware('can:Register-Received-Item-Details');
            Route::put('/admin/store/general-supply/received/{id}', [SalesController::class, 'gsReceivedUpdate'])->name('sales.gs.received.update')->middleware('can:Edit-Received-Item-Details');

            Route::get('/admin/store/reports', [SalesController::class, 'storeReports'])->name('sales.store.reports.index')->middleware('can:View-Store-Records-Reports');
            Route::get('/admin/store/general-supply/requested', [SalesController::class, 'gsRequestedIndex'])->name('sales.gs.requested.index')->middleware('can:View-Requested-Items-Details');
            Route::post('/admin/store/general-supply/issue', [SalesController::class, 'gsIssueStore'])->name('sales.gs.issue.store')->middleware('can:Issue-Requested-Items');
            Route::get('/admin/store/general-supply/issued', [SalesController::class, 'gsIssuedIndex'])->name('sales.gs.issued.index')->middleware('can:View-Issued-Items-Details');
            Route::get('/admin/store/general-supply/stock', [SalesController::class, 'gsStockIndex'])->name('sales.gs.stock.index')->middleware('can:View-Items-Stock-Details');
            Route::get('/admin/store/reports',[SalesController::class,'storeReportsIndex'])->name('store.reports');
            Route::get('/admin/store/reports', [SalesController::class,'storeReportsIndex'])->name('sales.store.reports.index');
            Route::get('/admin/store/general-supply/purchase-report', [SalesController::class, 'gsPurchaseReport'])->name('sales.gs.purchase.report')->middleware('can:View-Purchase-Report');
            // REQUISITION
            Route::get('/admin/reqsts/general-supply/requisition', [RequisitionController::class, 'gsRequisitionIndex'])->name('req.gs.index')->middleware('can:View-Requested-Items-Details');
            Route::post('/admin/reqsts/general-supply/requisition', [RequisitionController::class, 'gsRequisitionStore'])->name('req.gs.store')->middleware('can:Register-Requested-Items-Details');
            Route::put('/admin/reqsts/general-supply/requisition/{id}', [RequisitionController::class, 'gsRequisitionUpdate'])->name('req.gs.update')->middleware('can:Edit-Requested-Items-Details');
            Route::get('/admin/reqsts/general-supply/requisition/remove/{id}', [RequisitionController::class, 'gsRequisitionDestroy'])->name('req.gs.remove')->middleware('can:Delete-Requested-Items-Details');
            Route::get('/admin/reqsts/general-supply/requisition-report', [RequisitionController::class, 'gsRequisitionReport'])->name('req.gs.report')->middleware('can:View-Requisition-Report');
            Route::put('/admin/reqsts/general-supply/requisition/confirm-receipt/{id}', [RequisitionController::class, 'gsRequisitionConfirmReceipt'])->name('req.gs.confirm.receipt')->middleware('can:Confirm-Received-Requested-Items');
            // AJAX
            Route::get('/admin/reqsts/general-supply/ajax/descriptions/{itemId}', [RequisitionController::class, 'gsAjaxDescriptionsByItem'])->name('req.gs.ajax.descriptions');
            Route::post('/admin/reqsts/general-supply/ajax/available-stock', [RequisitionController::class, 'gsAjaxAvailableStock'])->name('req.gs.ajax.available.stock');
            // AJAX - keep your existing routes and add these below them
            Route::get('/admin/reqsts/general-supply/ajax/company-units/{companyId}', [RequisitionController::class, 'gsAjaxCompanyUnitsByCompany'])->name('req.gs.ajax.company.units');
            Route::get('/admin/reqsts/general-supply/ajax/work-points/{unitId}', [RequisitionController::class, 'gsAjaxWorkPointsByUnit'])->name('req.gs.ajax.work.points');
            Route::get('/admin/reqsts/general-supply/ajax/sections/{departmentId}', [RequisitionController::class, 'gsAjaxSectionsByDepartment'])->name('req.gs.ajax.sections');
            // Manufacturing
            Route::get('/manufacturing', [ManfctrContoller::class, 'manufacturing'])->name('manufacturing')->middleware('can:Inventory-Manufacturing-Modules');
            // Raw Materials
            Route::get('/admin/manfctr/raw-materials', [ManfctrContoller::class, 'rawMaterials'])->name('manfctr.rawmaterials.index')->middleware('can:View-Raw-Material');
            Route::post('/admin/manfctr/raw-materials', [ManfctrContoller::class, 'storeRawMaterial'])->name('manfctr.rawmaterials.store')->middleware('can:Register-Raw-Material');
            Route::put('/admin/manfctr/raw-materials/{id}', [ManfctrContoller::class, 'updateRawMaterial'])->name('manfctr.rawmaterials.update')->middleware('can:Edit-Raw-Material');
            Route::get('/admin/manfctr/raw-materials/remove/{id}', [ManfctrContoller::class, 'removeRawMaterial'])->name('manfctr.rawmaterials.remove')->middleware('can:Delete-Raw-Material');
            // Raw Prices
            Route::get('/admin/manfctr/raw-prices', [ManfctrContoller::class, 'rawPrices'])->name('manfctr.rawprices.index')->middleware('can:View-Raw-Material-Price');
            Route::post('/admin/manfctr/raw-prices', [ManfctrContoller::class, 'storeRawPrice'])->name('manfctr.rawprices.store')->middleware('can:Register-Raw-Material-Price');
            Route::put('/admin/manfctr/raw-prices/{id}', [ManfctrContoller::class, 'updateRawPrice'])->name('manfctr.rawprices.update')->middleware('can:Edit-Raw-Material-Price');
            Route::get('/admin/manfctr/raw-prices/remove/{id}', [ManfctrContoller::class, 'removeRawPrice'])->name('manfctr.rawprices.remove')->middleware('can:Delete-Raw-Material-Price');
            // REQUESTS
            Route::get('/admin/manfctr/raw-material-requests', [ManfctrContoller::class, 'requestIndex'])->name('manfctr.requests.index')->middleware('can:View-Manufacturing-Raw-Material-Request');
            Route::post('/admin/manfctr/raw-material-requests', [ManfctrContoller::class, 'requestStore'])->name('manfctr.requests.store')->middleware('can:Register-Manufacturing-Raw-Material-Request');
            Route::put('/admin/manfctr/raw-material-requests/{id}', [ManfctrContoller::class, 'requestUpdate'])->name('manfctr.requests.update')->middleware('can:Edit-Manufacturing-Raw-Material-Request');
            Route::get('/admin/manfctr/raw-material-requests/remove/{id}', [ManfctrContoller::class, 'requestDestroy'])->name('manfctr.requests.remove')->middleware('can:Delete-Manufacturing-Raw-Material-Request');
            // RECEIPTS FROM STORE
            Route::get('/admin/manfctr/raw-material-receipts', [ManfctrContoller::class, 'receiptIndex'])->name('manfctr.receipts.index')->middleware('can:View-Manufacturing-Receipts');
            Route::post('/admin/manfctr/raw-material-receipts', [ManfctrContoller::class, 'receiptStore'])->name('manfctr.receipts.store')->middleware('can:Register-Manufacturing-Receipts');
            Route::put('/admin/manfctr/raw-material-receipts/{id}', [ManfctrContoller::class, 'receiptUpdate'])->name('manfctr.receipts.update')->middleware('can:Edit-Manufacturing-Receipts');
            Route::get('/admin/manfctr/raw-material-receipts/remove/{id}', [ManfctrContoller::class, 'receiptDestroy'])->name('manfctr.receipts.remove')->middleware('can:Delete-Manufacturing-Receipts');
            // STOCK
            Route::get('/admin/manfctr/raw-material-stock', [ManfctrContoller::class, 'stockIndex'])->name('manfctr.stock.index')->middleware('can:View-Manufacturing-Stock');
            // STOCK MOVEMENT
            Route::get('/admin/manfctr/raw-material-stock-movement', [ManfctrContoller::class, 'stockMovementIndex'])->name('manfctr.stock.movement.index')->middleware('can:View-Manufacturing-Stock-Movement');
            // CONSUMPTION
            Route::get('/admin/manfctr/raw-material-consumption', [ManfctrContoller::class, 'consumptionIndex'])->name('manfctr.consumption.index')->middleware('can:View-Manufacturing-Consumption');
            Route::post('/admin/manfctr/raw-material-consumption', [ManfctrContoller::class, 'consumptionStore'])->name('manfctr.consumption.store')->middleware('can:Register-Manufacturing-Consumption');
            Route::put('/admin/manfctr/raw-material-consumption/{id}', [ManfctrContoller::class, 'consumptionUpdate'])->name('manfctr.consumption.update')->middleware('can:Edit-Manufacturing-Consumption');
            Route::get('/admin/manfctr/raw-material-consumption/remove/{id}', [ManfctrContoller::class, 'consumptionDestroy'])->name('manfctr.consumption.remove')->middleware('can:Delete-Manufacturing-Consumption');
            // Products
            Route::get('/admin/manfctr/products', [ManfctrContoller::class, 'products'])->name('manfctr.products.index')->middleware('can:View-Product');
            Route::post('/admin/manfctr/products', [ManfctrContoller::class, 'storeProduct'])->name('manfctr.products.store')->middleware('can:Register-Product');
            Route::put('/admin/manfctr/products/{id}', [ManfctrContoller::class, 'updateProduct'])->name('manfctr.products.update')->middleware('can:Edit-Product');
            Route::get('/admin/manfctr/products/remove/{id}', [ManfctrContoller::class, 'removeProduct'])->name('manfctr.products.remove')->middleware('can:Delete-Product');
            // Product Prices
            Route::get('/admin/manfctr/prd-prices', [ManfctrContoller::class, 'prdPrices']) ->name('manfctr.prdprices.index')->middleware('can:View-Product-Price');
            Route::post('/admin/manfctr/prd-prices', [ManfctrContoller::class, 'storePrdPrice'])->name('manfctr.prdprices.store')->middleware('can:Register-Product-Price');
            Route::put('/admin/manfctr/prd-prices/{id}', [ManfctrContoller::class, 'updatePrdPrice'])->name('manfctr.prdprices.update')->middleware('can:Edit-Product-Price');
            Route::get('/admin/manfctr/prd-prices/remove/{id}', [ManfctrContoller::class, 'removePrdPrice'])->name('manfctr.prdprices.remove')->middleware('can:Delete-Product-Price');
            // Packed products
            Route::get('/admin/manfctr/packed-products', [ManfctrContoller::class, 'packedPrds'])->name('manfctr.packed.index')->middleware('can:View-Packed-Product');
            Route::post('/admin/manfctr/packed-products', [ManfctrContoller::class, 'storePackedPrd'])->name('manfctr.packed.store')->middleware('can:Register-Packed-Product');
            Route::put('/admin/manfctr/packed-products/{id}', [ManfctrContoller::class, 'updatePackedPrd'])->name('manfctr.packed.update')->middleware('can:Edit-Packed-Product');
            Route::get('/admin/manfctr/packed-products/remove/{id}', [ManfctrContoller::class, 'removePackedPrd'])->name('manfctr.packed.remove')->middleware('can:Delete-Packed-Product');
            Route::get('/store-dashboard', [ManfctrContoller::class, 'dashboard'])->middleware(['auth','permission:Store-Management-Modules'])->name('store.management.dashboard');
            // Product stocks (view only)
            Route::get('/admin/manfctr/product-stocks', [ManfctrContoller::class, 'prdStocks'])->name('manfctr.stocks.index')->middleware('can:View-Product-Stock');
            // Product orders
            Route::get('/admin/manfctr/product-orders', [ManfctrContoller::class, 'prdOrders'])->name('manfctr.orders.index')->middleware('can:View-Product-Order');
            Route::post('/admin/manfctr/product-orders', [ManfctrContoller::class, 'storePrdOrder'])->name('manfctr.orders.store')->middleware('can:Register-Product-Order');
            Route::put('/admin/manfctr/product-orders/{id}', [ManfctrContoller::class, 'updatePrdOrder'])->name('manfctr.orders.update')->middleware('can:Edit-Product-Order');
            Route::get('/admin/manfctr/product-orders/remove/{id}', [ManfctrContoller::class, 'removePrdOrder'])->name('manfctr.orders.remove')->middleware('can:Delete-Product-Order');
            // Issue products
            Route::get('/admin/manfctr/issue-products', [ManfctrContoller::class, 'issPrds'])->name('manfctr.iss.index')->middleware('can:View-Issued-Product');
            Route::post('/admin/manfctr/issue-products', [ManfctrContoller::class, 'storeIssPrd'])->name('manfctr.iss.store')->middleware('can:Register-Issued-Product');
            Route::put('/admin/manfctr/issue-products/{id}', [ManfctrContoller::class, 'updateIssPrd'])->name('manfctr.iss.update')->middleware('can:Edit-Issued-Product');
            Route::get('/admin/manfctr/issue-products/remove/{id}', [ManfctrContoller::class, 'removeIssPrd'])->name('manfctr.iss.remove')->middleware('can:Delete-Issued-Product');
            Route::get('/admin/manfctr/product-stock-movement', [ManfctrContoller::class, 'prdStockMovement'])->name('manfctr.prdstockmovement.index')->middleware('can:View-Product-Stock-Movement');
            // CRM
            Route::get('/crm', [CrmSplyContoller::class, 'crm'])->name('crm')->middleware('can:CRM-Modules');
            // CSTM (customers & suppliers)
           /* Route::get('/admin/crm/customers', [CrmSplyContoller::class,'indexCstm'])->name('crm.cstm.index')->middleware('can:View-CustomerSupplier');
           Route::post('/admin/crm/customers', [CrmSplyContoller::class,'storeCstm'])->name('crm.cstm.store')->middleware('can:Register-CustomerSupplier');
           Route::put('/admin/crm/customers/{id}', [CrmSplyContoller::class,'updateCstm'])->name('crm.cstm.update')->middleware('can:Edit-CustomerSupplier');
           Route::get('/admin/crm/customers/remove/{id}', [CrmSplyContoller::class,'removeCstm'])->name('crm.cstm.remove')->middleware('can:Delete-CustomerSupplier');
           Route::get('/admin/crm/customers/{id}', [CrmSplyContoller::class,'showCstm'])->name('crm.cstm.show')->middleware('can:View-CustomerSupplier'); */
            // Orders
            Route::get('/admin/crm/orders', [CrmSplyContoller::class,'indexOrders'])->name('crm.orders.index')->middleware('can:View-Customer-Orders');
            Route::post('/admin/crm/orders', [CrmSplyContoller::class,'storeOrder'])->name('crm.orders.store')->middleware('can:Register-Customer-Orders');
            Route::put('/admin/crm/orders/{id}', [CrmSplyContoller::class,'updateOrder'])->name('crm.orders.update')->middleware('can:Edit-Customer-Orders');
            Route::get('/admin/crm/orders/remove/{id}', [CrmSplyContoller::class,'removeOrder'])->name('crm.orders.remove')->middleware('can:Delete-Customer-Orders');
            Route::get('/admin/crm/orders/{id}', [CrmSplyContoller::class,'showOrder'])->name('crm.orders.show')->middleware('can:View-Customer-Orders');
            // Order items (cstm_products)
            Route::get('/admin/crm/order-items/{order?}', [CrmSplyContoller::class,'indexOrderItems'])->name('crm.items.index')->middleware('can:View-Customer-OrderItems');
            Route::post('/admin/crm/order-items', [CrmSplyContoller::class,'storeOrderItem'])->name('crm.items.store')->middleware('can:Register-Customer-OrderItems');
            Route::put('/admin/crm/order-items/{id}', [CrmSplyContoller::class,'updateOrderItem'])->name('crm.items.update')->middleware('can:Edit-Customer-OrderItems');
            Route::get('/admin/crm/order-items/remove/{id}', [CrmSplyContoller::class,'removeOrderItem'])->name('crm.items.remove')->middleware('can:Delete-Customer-OrderItems');
            // Ledger / transactions
            Route::get('/admin/crm/tx', [CrmSplyContoller::class,'indexTx'])->name('crm.tx.index')->middleware('can:View-Customer-Ledger');
            Route::post('/admin/crm/tx', [CrmSplyContoller::class,'storeTx'])->name('crm.tx.store')->middleware('can:Register-Customer-Ledger');
            Route::put('/admin/crm/tx/{id}', [CrmSplyContoller::class,'updateTx'])->name('crm.tx.update')->middleware('can:Edit-Customer-Ledger');
            Route::get('/admin/crm/tx/remove/{id}', [CrmSplyContoller::class,'removeTx'])->name('crm.tx.remove')->middleware('can:Delete-Customer-Ledger');
            // Reports
            Route::get('/reports/customers', [CrmSplyContoller::class, 'reportsIndex'])->name('crm.reports.index')->middleware('can:View-CustomerSupplier-Reports');
            Route::get('/reports/customers/{id}', [CrmSplyContoller::class, 'reportCustomerDetail'])->name('crm.reports.customer.detail')->middleware('can:View-CustomerSupplier-Reports');
            // Optional endpoints for AJAX filtering (if you want)
            Route::post('/reports/customer-transactions', [CrmSplyContoller::class, 'ajaxCustomerTransactions'])->name('crm.reports.customer.transactions')->middleware('can:View-CustomerSupplier-Reports');
            //Hired Equipment
            Route::get('/hired-equipment', [CrmSplyContoller::class,'hiredIndex'])->name('hired.index')->middleware('can:View-Hired-Equipment-Details');
            Route::post('/hired-equipment', [CrmSplyContoller::class,'storeHired'])->name('hired.store')->middleware('can:Register-Hired-Equipment');
            Route::put('/hired-equipment/{id}', [CrmSplyContoller::class,'updateHired'])->name('hired.update')->middleware('can:Edit-Hired-Equipment');
            Route::get('/hired-equipment/remove/{id}', [CrmSplyContoller::class,'removeHired'])->name('hired.remove')->middleware('can:Delete-Hired-Equipment');
            // Hired equipment workings
            Route::get('/hired-equipment/workings', [CrmSplyContoller::class,'indexHiredWrk'])->name('hired.workings.index')->middleware('can:View-Hired-Equipment-Workings');
            Route::post('/hired-equipment/workings', [CrmSplyContoller::class,'storeHiredWrk'])->name('hired.workings.store')->middleware('can:Register-Hired-Equipment-Working');
            Route::put('/hired-equipment/workings/{id}', [CrmSplyContoller::class,'updateHiredWrk'])->name('hired.workings.update')->middleware('can:Edit-Hired-Equipment-Working');
            Route::get('/hired-equipment/workings/remove/{id}', [CrmSplyContoller::class,'removeHiredWrk'])->name('hired.workings.remove')->middleware('can:Delete-Hired-Equipment-Working');
            Route::get('/hired-equipment/workings/mark-paid/{id}', [CrmSplyContoller::class, 'markAsPaidHiredWrk'])->name('hired.workings.markpaid')->middleware('can:Change-Hired-Equipment-PaymentStatus');
            Route::get('/hired-equipment/reports', [CrmSplyContoller::class, 'reportHiredWrk'])->name('hired.workings.reports')->middleware('can:View-Hired-Equipment-Reports');
            Route::get('/hired-equipment/reports/export', [CrmSplyContoller::class, 'exportReportHiredWrk'])->name('hired.workings.reports.export')->middleware('can:Export-Hired-Equipment-Reports');
            // Auditing
            Route::get('/auditing', [AdminController::class, 'auditing']) ->name('auditing')->middleware('can:Auditing-Modules');
            // Quality Assurance
            Route::get('/quality-assurance', [AdminController::class, 'qualityAssurance'])->name('quality-assurance') ->middleware('can:QualityAssurance-Modules');
            // Microfinancing
            Route::get('/microfinancing', [MicrofinanceController::class, 'microfinancing'])->name('microfinancing')->middleware('can:Microfinancing-Modules');  
            // DASHBOARD
            Route::get('/admin/micro/dashboard', [MicrofinanceController::class, 'dashboard'])->name('micro.dashboard')->middleware('can:View-Microfinance-Dashboard');
            // SETTINGS
            Route::get('/admin/micro/settings', [MicrofinanceController::class, 'indexSettings'])->name('micro.settings.index')->middleware('can:View-Microfinance-Settings');
            Route::post('/admin/micro/settings', [MicrofinanceController::class, 'storeSettings'])->name('micro.settings.store')->middleware('can:Register-Microfinance-Settings');
            Route::put('/admin/micro/settings/{id}', [MicrofinanceController::class, 'updateSettings'])->name('micro.settings.update')->middleware('can:Edit-Microfinance-Settings');
            // LOAN CATEGORIES
            Route::get('/admin/micro/loan-categories', [MicrofinanceController::class, 'indexLoanCategories'])->name('micro.loan_categories.index')->middleware('can:View-Loan-Categories');
            Route::post('/admin/micro/loan-categories', [MicrofinanceController::class, 'storeLoanCategory'])->name('micro.loan_categories.store')->middleware('can:Register-Loan-Categories');
            Route::put('/admin/micro/loan-categories/{id}', [MicrofinanceController::class, 'updateLoanCategory'])->name('micro.loan_categories.update')->middleware('can:Edit-Loan-Categories');
            Route::get('/admin/micro/loan-categories/remove/{id}', [MicrofinanceController::class, 'removeLoanCategory'])->name('micro.loan_categories.remove')->middleware('can:Delete-Loan-Categories');
            // LOAN PRODUCTS
            Route::get('/admin/micro/loan-products', [MicrofinanceController::class, 'indexLoanProducts'])->name('micro.loan_products.index')->middleware('can:View-Loan-Products');
            Route::post('/admin/micro/loan-products', [MicrofinanceController::class, 'storeLoanProduct'])->name('micro.loan_products.store')->middleware('can:Register-Loan-Products');
            Route::put('/admin/micro/loan-products/{id}', [MicrofinanceController::class, 'updateLoanProduct'])->name('micro.loan_products.update')->middleware('can:Edit-Loan-Products');
            Route::get('/admin/micro/loan-products/remove/{id}', [MicrofinanceController::class, 'removeLoanProduct'])->name('micro.loan_products.remove')->middleware('can:Delete-Loan-Products');
            // APPLICANTS
            Route::get('/admin/micro/applicants', [MicrofinanceController::class, 'indexApplicants'])->name('micro.applicants.index')->middleware('can:View-Loan-Applicants');
            Route::post('/admin/micro/applicants', [MicrofinanceController::class, 'storeApplicant'])->name('micro.applicants.store')->middleware('can:Register-Loan-Applicants');
            Route::put('/admin/micro/applicants/{id}', [MicrofinanceController::class, 'updateApplicant'])->name('micro.applicants.update')->middleware('can:Edit-Loan-Applicants');
            Route::get('/admin/micro/applicants/remove/{id}', [MicrofinanceController::class, 'removeApplicant'])->name('micro.applicants.remove')->middleware('can:Delete-Loan-Applicants');
            // APPLICATIONS
            Route::get('/admin/micro/applications', [MicrofinanceController::class, 'indexApplications'])->name('micro.applications.index')->middleware('can:View-Loan-Applications');
            Route::get('/admin/micro/applications/create', [MicrofinanceController::class, 'createApplication'])->name('micro.applications.create')->middleware('can:Register-Loan-Applications');
            Route::post('/admin/micro/applications', [MicrofinanceController::class, 'storeApplication'])->name('micro.applications.store')->middleware('can:Register-Loan-Applications');
            Route::get('/admin/micro/applications/{id}', [MicrofinanceController::class, 'showApplication'])->name('micro.applications.show')->middleware('can:View-Loan-Applications');
            Route::get('/admin/micro/applications/{id}/edit', [MicrofinanceController::class, 'editApplication'])->name('micro.applications.edit')->middleware('can:Edit-Loan-Applications');
            Route::put('/admin/micro/applications/{id}', [MicrofinanceController::class, 'updateApplication'])->name('micro.applications.update')->middleware('can:Edit-Loan-Applications');
            Route::get('/admin/micro/applications/remove/{id}', [MicrofinanceController::class, 'removeApplication'])->name('micro.applications.remove')->middleware('can:Delete-Loan-Applications');
            // VERIFICATION / APPROVAL / CASHOUT
            Route::post('/admin/micro/applications/{id}/verify', [MicrofinanceController::class, 'verifyApplication'])->name('micro.applications.verify')->middleware('can:Verify-Loan-Applications');
            Route::post('/admin/micro/applications/{id}/decline', [MicrofinanceController::class, 'declineApplication'])->name('micro.applications.decline')->middleware('can:Decline-Loan-Applications');
            Route::post('/admin/micro/applications/{id}/approve', [MicrofinanceController::class, 'approveApplication'])->name('micro.applications.approve')->middleware('can:Approve-Loan-Applications');
            Route::post('/admin/micro/applications/{id}/reject', [MicrofinanceController::class, 'rejectApplication'])->name('micro.applications.reject')->middleware('can:Reject-Loan-Applications');
            Route::post('/admin/micro/applications/{id}/cashout', [MicrofinanceController::class, 'cashoutApplication'])->name('micro.applications.cashout')->middleware('can:Cashout-Loan-Applications');
            // APPLICATION SUB ITEMS
            Route::post('/admin/micro/applications/{id}/guarantors', [MicrofinanceController::class, 'storeGuarantor'])->name('micro.applications.guarantors.store')->middleware('can:Register-Loan-Applications');
            Route::get('/admin/micro/guarantors/remove/{id}', [MicrofinanceController::class, 'removeGuarantor'])->name('micro.guarantors.remove')->middleware('can:Edit-Loan-Applications');
            Route::post('/admin/micro/applications/{id}/collaterals', [MicrofinanceController::class, 'storeCollateral'])->name('micro.applications.collaterals.store')->middleware('can:Register-Loan-Collateral');
            Route::get('/admin/micro/collaterals/remove/{id}', [MicrofinanceController::class, 'removeCollateral'])->name('micro.collaterals.remove')->middleware('can:Delete-Loan-Collateral');
            Route::post('/admin/micro/applications/{id}/attachments', [MicrofinanceController::class, 'storeApplicationAttachment'])->name('micro.applications.attachments.store')->middleware('can:Register-Loan-Attachments');
            Route::get('/admin/micro/attachments/remove/{id}', [MicrofinanceController::class, 'removeAttachment'])->name('micro.attachments.remove')->middleware('can:Delete-Loan-Attachments');
            // REPAYMENTS
            Route::get('/admin/micro/repayments', [MicrofinanceController::class, 'indexRepayments'])->name('micro.repayments.index')->middleware('can:View-Loan-Repayments');
            Route::post('/admin/micro/repayments', [MicrofinanceController::class, 'storeRepayment'])->name('micro.repayments.store')->middleware('can:Register-Loan-Repayments');
            Route::put('/admin/micro/repayments/{id}', [MicrofinanceController::class, 'updateRepayment'])->name('micro.repayments.update')->middleware('can:Edit-Loan-Repayments');
            Route::get('/admin/micro/repayments/remove/{id}', [MicrofinanceController::class, 'removeRepayment'])->name('micro.repayments.remove')->middleware('can:Delete-Loan-Repayments');
            // PENALTIES
            Route::get('/admin/micro/penalties', [MicrofinanceController::class, 'indexPenalties'])->name('micro.penalties.index')->middleware('can:View-Loan-Penalties');
            Route::post('/admin/micro/penalties', [MicrofinanceController::class, 'storePenalty'])->name('micro.penalties.store')->middleware('can:Register-Loan-Penalties');
            Route::put('/admin/micro/penalties/{id}', [MicrofinanceController::class, 'updatePenalty'])->name('micro.penalties.update')->middleware('can:Edit-Loan-Penalties');
            Route::get('/admin/micro/penalties/remove/{id}', [MicrofinanceController::class, 'removePenalty'])->name('micro.penalties.remove')->middleware('can:Delete-Loan-Penalties');
            // REMINDERS
            Route::get('/admin/micro/reminders', [MicrofinanceController::class, 'indexReminders'])->name('micro.reminders.index')->middleware('can:View-Loan-Reminders');
            Route::post('/admin/micro/reminders/send/{id}', [MicrofinanceController::class, 'sendReminder'])->name('micro.reminders.send')->middleware('can:Send-Loan-Reminders');
            // COSTS
            Route::get('/admin/micro/costs', [MicrofinanceController::class, 'indexCosts'])->name('micro.costs.index')->middleware('can:View-Microfinance-Costs');
            Route::post('/admin/micro/costs', [MicrofinanceController::class, 'storeCost'])->name('micro.costs.store')->middleware('can:Register-Microfinance-Costs');
            Route::put('/admin/micro/costs/{id}', [MicrofinanceController::class, 'updateCost'])->name('micro.costs.update')->middleware('can:Edit-Microfinance-Costs');
            Route::get('/admin/micro/costs/remove/{id}', [MicrofinanceController::class, 'removeCost'])->name('micro.costs.remove')->middleware('can:Delete-Microfinance-Costs');
            // OTHER INCOME
            Route::get('/admin/micro/other-income', [MicrofinanceController::class, 'indexOtherIncome'])->name('micro.other_income.index')->middleware('can:View-Microfinance-Other-Income');
            Route::post('/admin/micro/other-income', [MicrofinanceController::class, 'storeOtherIncome'])->name('micro.other_income.store')->middleware('can:Register-Microfinance-Other-Income');
            Route::put('/admin/micro/other-income/{id}', [MicrofinanceController::class, 'updateOtherIncome'])->name('micro.other_income.update')->middleware('can:Edit-Microfinance-Other-Income');
            Route::get('/admin/micro/other-income/remove/{id}', [MicrofinanceController::class, 'removeOtherIncome'])->name('micro.other_income.remove')->middleware('can:Delete-Microfinance-Other-Income');
            // REPORTS
            Route::get('/admin/micro/reports', [MicrofinanceController::class, 'indexReports'])->name('micro.reports.index')->middleware('can:View-Microfinance-Reports');
            // BankNetwork
            Route::get('/admin/micro/bn', [MicrofinanceController::class,'indexBankNetworks'])->name('micro.bank_networks.index')->middleware('can:View-BankNetwork');
            Route::post('/admin/micro/bn', [MicrofinanceController::class,'storeBankNetwork'])->name('micro.bank_networks.store')->middleware('can:Register-BankNetwork');
            Route::put('/admin/micro/bn/{id}', [MicrofinanceController::class,'updateBankNetwork'])->name('micro.bank_networks.update')->middleware('can:Edit-BankNetwork');
            Route::get('/admin/micro/bn/remove/{id}', [MicrofinanceController::class,'removeBankNetwork'])->name('micro.bank_networks.remove')->middleware('can:Delete-BankNetwork');
            // Transactions
            Route::get('/admin/micro/transactions', [MicrofinanceController::class,'indexTransactions'])->name('micro.transactions.index')->middleware('can:View-Microfinancing-Transaction');
            Route::post('/admin/micro/transactions', [MicrofinanceController::class,'storeTransaction'])->name('micro.transactions.store')->middleware('can:Register-Microfinancing-Transaction');
            Route::get('/admin/micro/transactions/show/{id}', [MicrofinanceController::class,'showTransaction'])->name('micro.transactions.show')->middleware('can:Edit-Microfinancing-Transaction'); // returns json for edit modal
            Route::put('/admin/micro/transactions/{id}', [MicrofinanceController::class,'updateTransaction'])->name('micro.transactions.update')->middleware('can:Edit-Microfinancing-Transaction');
            Route::get('/admin/micro/transactions/remove/{id}', [MicrofinanceController::class,'removeTransaction'])->name('micro.transactions.remove')->middleware('can:Delete-Microfinancing-Transaction');
            // Reports
            Route::get('/admin/micro/reports/daily', [MicrofinanceController::class,'dailySummary'])->name('micro.reports.daily')->middleware('can:View-Microfinancing-Reports');
            Route::get('/admin/micro/reports/bank-network', [MicrofinanceController::class,'bankNetworkReport'])->name('micro.reports.bn')->middleware('can:View-Microfinancing-Reports');
            Route::get('/admin/micro/reports/bn-detailed', [MicrofinanceController::class, 'bankNetworkReportDetailed'])->name('micro.reports.bn.detail')->middleware('can:View-Microfinancing-Reports');
            // Consultancy
            Route::get('/consultancy', [AdminController::class, 'consultancy'])->name('consultancy')->middleware('can:Consultancy-Modules');
            // Reporting
            Route::get('/reporting', [AdminController::class, 'reporting'])->name('reporting')->middleware('can:Reporting-Modules');
            // Requistion
            Route::get('/requisition', [RequisitionController::class, 'requisition'])->name('requisition')->middleware('can:Requisition-Modules');
            // Money Requests
            Route::get('/admin/moneyrequest', [RequisitionController::class, 'moneyIndex'])->name('moneyrequest.index')->middleware('can:View-MoneyRequest');
            Route::post('/admin/moneyrequest', [RequisitionController::class, 'moneyStore'])->name('moneyrequest.store')->middleware('can:Register-MoneyRequest');
            Route::get('/admin/moneyrequest/show/{id}', [RequisitionController::class, 'moneyShow'])->name('moneyrequest.show')->middleware('can:View-MoneyRequest');
            Route::get('/admin/moneyrequest/print/{id}', [RequisitionController::class, 'moneyPrint'])->name('moneyrequest.print')->middleware('can:Print-MoneyRequest');
            Route::get('/admin/moneyrequest/edit/{id}', [RequisitionController::class, 'moneyEdit'])->name('moneyrequest.edit')->middleware('can:Edit-MoneyRequest');
            Route::put('/admin/moneyrequest/{id}', [RequisitionController::class, 'moneyUpdate'])->name('moneyrequest.update')->middleware('can:Edit-MoneyRequest');
            Route::get('/admin/moneyrequest/remove/{id}', [RequisitionController::class, 'moneyRemove'])->name('moneyrequest.remove')->middleware('can:Delete-MoneyRequest');
            Route::post('/admin/moneyrequest/verify/{id}', [RequisitionController::class, 'moneyVerify'])->name('moneyrequest.verify')->middleware('can:Verify-MoneyRequest');
            Route::post('/admin/moneyrequest/approve/{id}', [RequisitionController::class, 'moneyApprove'])->name('moneyrequest.approve')->middleware('can:Approve-MoneyRequest');
            Route::post('/admin/moneyrequest/reject/{id}', [RequisitionController::class, 'moneyReject'])->name('moneyrequest.reject')->middleware('can:Verify-MoneyRequest');
            Route::post('/admin/moneyrequest/cashout/{id}', [RequisitionController::class, 'moneyCashOut'])->name('moneyrequest.cashout')->middleware('can:CashOut-MoneyRequest');
            Route::post('/admin/moneyrequest/retire/{id}', [RequisitionController::class, 'moneyRetire'])->name('moneyrequest.retire')->middleware('can:Retirement-MoneyRequest');
            Route::get('/admin/reports/requisitions',[RequisitionController::class, 'requisitionReport'])->name('reports.requisitions')->middleware('can:View-Requisition-Reports');
            // cash-out / retirement report
            Route::get('/admin/reports/cashed-retired-money-requests', [RequisitionController::class, 'cashoutRetirementReport'])->name('reports.money.cashout_retirement')->middleware('can:View-Cashed-Out&Retirement-Money-Request-Report');
            Route::get('/admin/reports/cashed-retired-money-requests/show/{id}', [RequisitionController::class, 'cashoutRetirementShow'])->name('reports.money.cashout_retirement.show')->middleware('can:View-Cashed-Out&Retirement-Money-Request-Report');
            Route::get('/admin/reports/cashed-retired-money-requests/document/{id}', [RequisitionController::class, 'retirementDocument'])->name('reports.money.cashout_retirement.document')->middleware('can:View-Retirement-Money-Request-Document');
            Route::get('/admin/reports/requisition-book', [RequisitionController::class, 'requisitionBook'])->name('reports.requisition.book')->middleware('can:View-Requistion-Book-Details');
            Route::get('/reports/requisition-book/excel', [RequisitionController::class, 'requisitionBookExportExcel'])->name('reports.requisition.book.excel')->middleware('can:View-Requistion-Book-Details');
            //requisitions
            Route::get('/admin/moneyrequest/pending', [RequisitionController::class, 'moneyPending'])->name('moneyrequest.pending')->middleware('can:View-MoneyRequest');
            Route::get('/admin/moneyrequest/verified', [RequisitionController::class, 'moneyVerified'])->name('moneyrequest.verified')->middleware('can:Approve-MoneyRequest');
            Route::get('/admin/moneyrequest/approved', [RequisitionController::class, 'moneyApproved'])->name('moneyrequest.approved')->middleware('can:CashOut-MoneyRequest');
            Route::get('/admin/moneyrequest/rejected', [RequisitionController::class, 'moneyRejected'])->name('moneyrequest.rejected')->middleware('can:View-MoneyRequest');
            //New routes
            Route::post('/admin/moneyrequest/unverify/{id}', [RequisitionController::class, 'moneyUnverify'])->name('moneyrequest.unverify')->middleware('can:Verify-MoneyRequest');
            Route::post('/admin/moneyrequest/bulk-approve', [RequisitionController::class, 'moneyBulkApprove'])->name('moneyrequest.bulkapprove')->middleware('can:Approve-MoneyRequest');
            Route::post('/admin/moneyrequest/bulk-cashout', [RequisitionController::class, 'moneyBulkCashOut'])->name('moneyrequest.bulkcashout')->middleware('can:CashOut-MoneyRequest');
            // RAW MATERIALS
            Route::get('/admin/sales/raw-materials', [SalesController::class, 'rmIndex'])->name('sales.rm.index')->middleware('can:View-Raw-Materials');
            Route::post('/admin/sales/raw-materials', [SalesController::class, 'rmStore'])->name('sales.rm.store')->middleware('can:Register-Raw-Materials');
            Route::put('/admin/sales/raw-materials/{id}', [SalesController::class, 'rmUpdate'])->name('sales.rm.update')->middleware('can:Edit-Raw-Materials');
            Route::get('/admin/sales/raw-materials/remove/{id}', [SalesController::class, 'rmDestroy'])->name('sales.rm.remove')->middleware('can:Delete-Raw-Materials');
            // VENDORS
            Route::get('/admin/sales/vendors', [SalesController::class, 'vendorsIndex'])->name('sales.vendors.index')->middleware('can:View-Vendors');
            Route::post('/admin/sales/vendors', [SalesController::class, 'vendorsStore'])->name('sales.vendors.store')->middleware('can:Register-Vendors');
            Route::put('/admin/sales/vendors/{id}', [SalesController::class, 'vendorsUpdate'])->name('sales.vendors.update')->middleware('can:Edit-Vendors');
            Route::get('/admin/sales/vendors/remove/{id}', [SalesController::class, 'vendorsDestroy'])->name('sales.vendors.remove')->middleware('can:Delete-Vendors');
             // PURCHASE ORDERS
            Route::get('/admin/sales/purchase-orders', [SalesController::class, 'poIndex'])->name('sales.po.index')->middleware('can:View-Purchase-Orders');
            Route::post('/admin/sales/purchase-orders', [SalesController::class, 'poStore'])->name('sales.po.store')->middleware('can:Register-Purchase-Orders');
            Route::get('/admin/sales/purchase-orders/edit/{id}', [SalesController::class, 'poEdit'])->name('sales.po.edit')->middleware('can:Edit-Purchase-Orders');
            Route::put('/admin/sales/purchase-orders/{id}', [SalesController::class, 'poUpdate'])->name('sales.po.update')->middleware('can:Edit-Purchase-Orders');
            Route::get('/admin/sales/purchase-orders/show/{id}', [SalesController::class, 'poShow'])->name('sales.po.show')->middleware('can:View-Purchase-Orders');
            Route::get('/admin/sales/purchase-orders/print/{id}', [SalesController::class, 'poPrint'])->name('sales.po.print')->middleware('can:View-Purchase-Orders');
            Route::get('/admin/sales/purchase-orders/documents/{id}', [SalesController::class, 'poDocuments'])->name('sales.po.documents')->middleware('can:View-Purchase-Orders');
            Route::get('/admin/sales/purchase-orders/document/{id}/{type}/{action}', [SalesController::class, 'poDocumentFile'])->name('sales.po.document.file')->middleware('can:View-Purchase-Orders');
            Route::get('/admin/sales/purchase-orders/approve/{id}', [SalesController::class, 'poApprove'])->name('sales.po.approve')->middleware('can:Edit-Purchase-Orders');
            Route::get('/admin/sales/purchase-orders/reject/{id}', [SalesController::class, 'poReject'])->name('sales.po.reject')->middleware('can:Edit-Purchase-Orders');
            Route::post('/admin/sales/purchase-orders/receive/{id}', [SalesController::class, 'poReceive'])->name('sales.po.receive')->middleware('can:Edit-Purchase-Orders');
            Route::post('/admin/sales/purchase-orders/payment/{id}', [SalesController::class, 'poPayment'])->name('sales.po.payment')->middleware('can:Edit-Purchase-Orders');
            Route::get('/admin/sales/purchase-orders/remove/{id}', [SalesController::class, 'poDestroy'])->name('sales.po.remove')->middleware('can:Delete-Purchase-Orders');
            // PURCHASE ORDER AJAX
            Route::get('/admin/sales/purchase-orders/ajax/business-units/{company_id}', [SalesController::class, 'poBusinessUnits'])->name('sales.po.ajax.business.units');
            Route::get('/admin/sales/purchase-orders/ajax/work-points/{business_id}', [SalesController::class, 'poWorkPoints'])->name('sales.po.ajax.work.points');
            // CONTRACTS
            Route::get('/admin/sales/contracts', [SalesController::class, 'contractsIndex'])->name('sales.contracts.index')->middleware('can:View-Contracts');
            Route::post('/admin/sales/contracts', [SalesController::class, 'contractsStore'])->name('sales.contracts.store')->middleware('can:Register-Contracts');
            Route::put('/admin/sales/contracts/{id}', [SalesController::class, 'contractsUpdate'])->name('sales.contracts.update')->middleware('can:Edit-Contracts');
            Route::get('/admin/sales/contracts/remove/{id}', [SalesController::class, 'contractsDestroy'])->name('sales.contracts.remove')->middleware('can:Delete-Contracts');
            // RAW MATERIAL PURCHASES
            Route::get('/admin/sales/raw-material-purchases', [SalesController::class, 'rmPurchaseIndex'])->name('sales.rm.purchase.index')->middleware('can:View-Raw-Materials-Purchase');
            Route::post('/admin/sales/raw-material-purchases', [SalesController::class, 'rmPurchaseStore'])->name('sales.rm.purchase.store')->middleware('can:Register-Raw-Materials-Purchase');
            Route::put('/admin/sales/raw-material-purchases/{id}', [SalesController::class, 'rmPurchaseUpdate'])->name('sales.rm.purchase.update')->middleware('can:Edit-Raw-Materials-Purchase');
            Route::get('/admin/sales/raw-material-purchases/remove/{id}', [SalesController::class, 'rmPurchaseDestroy'])->name('sales.rm.purchase.remove')->middleware('can:Delete-Raw-Materials-Purchase');
            // RAW MATERIAL STOCK
            Route::get('/admin/sales/raw-material-stock', [SalesController::class, 'rmStockIndex'])->name('sales.rm.stock.index')->middleware('can:View-Raw-Material-Stock');
            // RAW MATERIAL ISSUES
            Route::get('/admin/sales/raw-material-issues', [SalesController::class, 'rmIssueIndex'])->name('sales.rm.issue.index')->middleware('can:View-Raw-Material-Issues'); 
            Route::post('/admin/sales/raw-material-issues', [SalesController::class, 'rmIssueStore'])->name('sales.rm.issue.store')->middleware('can:Register-Raw-Material-Issues');
            Route::put('/admin/sales/raw-material-issues/{id}', [SalesController::class, 'rmIssueUpdate'])->name('sales.rm.issue.update')->middleware('can:Edit-Raw-Material-Issues');
            Route::get('/admin/sales/raw-material-issues/remove/{id}', [SalesController::class, 'rmIssueDestroy'])->name('sales.rm.issue.remove')->middleware('can:Delete-Raw-Material-Issues'); 
             // STORE REPORTS
            Route::get('/admin/sales/store-reports', [SalesController::class, 'storeReportsIndex'])->name('sales.store.reports.index')->middleware('can:View-Store-Records-Reports');
            // STOCK AUDITS
            Route::get('/sales/stock-audits', [SalesController::class, 'stockAuditIndex'])->name('sales.stock.audit.index')->middleware('can:View-Stock-Audit');
            Route::post('/sales/stock-audits', [SalesController::class, 'stockAuditStore'])->name('sales.stock.audit.store')->middleware('can:Register-Stock-Audit');
            Route::put('/sales/stock-audits/{id}', [SalesController::class, 'stockAuditUpdate'])->name('sales.stock.audit.update')->middleware('can:Edit-Stock-Audit');
            Route::get('/sales/stock-audits/approve/{id}', [SalesController::class, 'stockAuditApprove'])->name('sales.stock.audit.approve')->middleware('can:Approve-Stock-Audit');
            Route::get('/sales/stock-audits/close/{id}', [SalesController::class, 'stockAuditClose'])->name('sales.stock.audit.close')->middleware('can:Approve-Stock-Audit');
            Route::get('/sales/stock-audits/remove/{id}', [SalesController::class, 'stockAuditDestroy'])->name('sales.stock.audit.remove')->middleware('can:Delete-Stock-Audit');
            Route::prefix('stock-management')->name('stock.management.')->controller(StockManagementController::class)->middleware(['auth', 'can:View-Stock-Management'])
                ->group(function () {
                    Route::get('/', 'dashboard')->name('dashboard')->middleware('can:View-Stock-Dashboard');
                    Route::get('/movement', 'movement')->name('movement.page')->middleware('can:View-Stock-Movement');
                    Route::post('/movement/store', 'stockMovement')->name('movement.store')->middleware('can:Create-Stock-Movement');
                    Route::post('/receive', 'receiveStock')->name('receive')->middleware('can:Create-Stock-In');
                    Route::post('/process-delivery/{id}', 'processDelivery')->name('process.delivery')->middleware('can:Create-Stock-Out');
                    Route::get('/adjustment', 'adjustmentIndex')->name('adjust')->middleware('can:View-Manufacturing-Stock');
                    Route::post('/adjustment/store', 'storeAdjustment')->name('adjust.store')->middleware('can:Create-Stock-Adjustment');
                    Route::get('/inventory-report', 'inventoryReport')->name('inventory.report')->middleware('can:View-Inventory-Reports');
                    Route::post('/adjustment/save', 'adjustStockSave')->name('adjust.save')->middleware('can:Create-Stock-Adjustment');
                    Route::get('/export', 'exportExcel')->name('export')->middleware('can:Export-Stock-Reports');
                    Route::post('/store/{module}', 'store')->name('store')->middleware('can:Create-Stock-Data');
                    Route::put('/update/{module}/{id}', 'update')->name('update')->middleware('can:Edit-Stock-Data');
                    Route::delete('/delete/{module}/{id}', 'destroy')->name('destroy')->middleware('can:Delete-Stock-Data');
                });
            // Direct raw material issue page
            Route::get('/stock/raw-material-issues', [StockManagementController::class, 'rawMaterialIssues'])->name('stock.raw.issue')->middleware(['auth', 'can:View-Stock-Management']);
            Route::get('/admin/deliveries/document-pack/{id}', [SalesInvoiceController::class, 'documentPack'])->name('sales.delivery.document.pack')->middleware('permission:View-Delivery');
            Route::get('/admin/deliveries/document-pack/{id}/download', [SalesInvoiceController::class, 'downloadDocumentPack'])->name('sales.delivery.document.pack.download')->middleware('permission:View-Delivery');
            Route::post('/stock/raw-material-issues', [StockManagementController::class, 'storeRawMaterialIssue'])->name('stock.raw.issue.store')->middleware(['auth', 'can:Create-Stock-Out']);
            //product
            Route::get('/admin/sales/stock-audits', [SalesController::class, 'stockAuditIndex'])->name('sales.stock.audit.index')->middleware('can:View-Stock-Audits');
            Route::post('/admin/sales/stock-audits', [SalesController::class, 'stockAuditStore'])->name('sales.stock.audit.store')->middleware('can:Register-Stock-Audits');
            Route::put('/admin/sales/stock-audits/{id}', [SalesController::class, 'stockAuditUpdate'])->name('sales.stock.audit.update')->middleware('can:Edit-Stock-Audits');
            Route::get('/admin/sales/stock-audits/remove/{id}', [SalesController::class, 'stockAuditDestroy'])->name('sales.stock.audit.remove')->middleware('can:Delete-Stock-Audits');
    
            Route::prefix('stock-management')->name('stock.management.')->controller(StockManagementController::class)->middleware(['auth','can:View-Stock-Management'])->group(function () {
            Route::get('/', 'dashboard')->name('dashboard')->middleware('can:View-Stock-Dashboard');
            Route::get('/module/{module}', 'module')->name('module')->middleware('can:View-Stock-Modules');
            Route::get('/movement', 'movement')->name('movement.page')->middleware('can:View-Stock-Movement');
            Route::post('/movement/store', 'stockMovement')->name('movement.store')->middleware('can:Create-Stock-Movement');
            Route::post('/stock-in', 'stockIn')->name('stock.in')->middleware('can:Create-Stock-In');
            Route::post('/receive', 'receiveStock')->name('receive')->middleware('can:Create-Stock-In');
            Route::post('/process-delivery/{id}', 'processDelivery')->name('process.delivery')->middleware('can:Create-Stock-Out');
            Route::post('/transfer/store', 'transferStock')->name('transfer.store')->middleware('can:Create-Stock-Movement');
            Route::get('/adjustment', 'adjustmentIndex')->name('adjust')->middleware('can:View-Manufacturing-Stock');
            Route::post('/adjustment/store', 'storeAdjustment')->name('adjust.store')->middleware('can:Create-Stock-Adjustment');
            Route::post('/adjustment/save', 'adjustStockSave')->name('adjust.save')->middleware('can:Create-Stock-Adjustment');

            Route::get('/stock-out', 'stockOut')->name('stock.out')->middleware('can:Create-Stock-Out');
            Route::post('/stock-out/store', 'stockOutStore')->name('stock.out.store')->middleware('can:Create-Stock-Out');

            Route::get('/export', 'exportExcel')->name('export')->middleware('can:Export-Stock-Reports');
            Route::post('/store/{module}', 'store')->name('store')->middleware('can:Create-Stock-Data');
            Route::put('/update/{module}/{id}', 'update')->name('update')->middleware('can:Edit-Stock-Data');
            Route::delete('/delete/{module}/{id}', 'destroy')->name('destroy')->middleware('can:Delete-Stock-Data');
                });
        
            //product
            Route::middleware(['auth'])->group(function () {
            // PRODUCTS
            Route::get('/products', [ProductController::class, 'index'])->name('products.index')->middleware('permission:View-Products');
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create')->middleware('permission:Create-Product');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:Create-Product');
            Route::get('/products/export', [ProductController::class, 'export'])->name('products.export')->middleware('permission:Export-Product');
            Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show')->middleware('permission:View-Products');
            Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('permission:Manage-Product');
            Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update')->middleware('permission:Manage-Product');
            Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.delete')->middleware('permission:Delete-Product');
            Route::get('/products/{id}/print', [ProductController::class, 'print'])->name('products.print')->middleware('permission:Print-Product');
            Route::post('/sales/store', [SalesController::class, 'store'])->name('sales.store')->middleware('permission:Create-Sales');
            Route::post('/admin/store/general-supply/descriptions/store', [App\Http\Controllers\SalesController::class, 'gsDescriptionsStore'])->name('gs.descriptions.store');
            //PurchaseManagementController
            Route::prefix('business/purchase')->middleware(['auth'])->group(function () {
            Route::get('/dashboard',[PurchaseManagementController::class,'dashboard'])->name('business.purchase.dashboard')->middleware('permission:View-Purchasing-Dashboard');
            });
            // LOSS PREVENTION
            Route::get('/admin/sales/loss-prevention', [SalesController::class, 'lossPreventionIndex'])->name('sales.loss.index')->middleware('can:View-Loss-Prevention');
            Route::prefix('admin/reqsts/general-supply')->group(function () {
            Route::get('/requisition', [RequisitionController::class, 'gsRequisitionIndex'])->name('req.gs.index');
            Route::post('/requisition', [RequisitionController::class, 'gsRequisitionStore']) ->name('req.gs.store');
            Route::put('/requisition/{id}', [RequisitionController::class, 'gsRequisitionUpdate'])->name('req.gs.update');
            Route::get('/requisition/remove/{id}', [RequisitionController::class, 'gsRequisitionDestroy'])->name('req.gs.remove');
            Route::put('/requisition/confirm-receipt/{id}', [RequisitionController::class, 'gsRequisitionConfirmReceipt']) ->name('req.gs.confirm.receipt');
            Route::get('/requisition-report', [RequisitionController::class, 'gsRequisitionReport'])->name('req.gs.report');
            Route::get('/ajax/descriptions/{itemId}', [RequisitionController::class, 'gsAjaxDescriptionsByItem'])->name('req.gs.ajax.descriptions');
            Route::post('/ajax/available-stock', [RequisitionController::class, 'gsAjaxAvailableStock'])->name('req.gs.ajax.available.stock');});
    
            Route::get('/sales/dashboard', [SalesManagementController::class, 'dashboard'])->name('sales.management.dashboard')->middleware('can:View-Sales-Dashboard');
            Route::get('/', [SalesManagementController::class, 'dashboard'])->name('sales.dashboard') ->middleware('can:View-Sales-Dashboard');
            // ================= ITEMS =================
            Route::get('/store/general-supply/items', [SalesManagementController::class, 'gsItemsIndex'])->name('sales.gs.items.index')->middleware('can:View-Items');
            Route::post('/store/general-supply/items', [SalesManagementController::class, 'gsItemsStore'])->name('sales.gs.items.store')->middleware('can:Create-Items');
            Route::put('/store/general-supply/items/{id}', [SalesManagementController::class, 'gsItemsUpdate'])->name('sales.gs.items.update')->middleware('can:Edit-Items');
            Route::delete('/store/general-supply/items/{id}', [SalesManagementController::class, 'gsItemsDestroy'])->name('sales.gs.items.delete')->middleware('can:Delete-Items');
            Route::get('/store/general-supply/stock', [SalesManagementController::class, 'gsStockIndex'])->name('sales.gs.stock.index')->middleware('can:View-Stock');
                    // ================= SALES =================
            Route::get('/sales/create', [SalesManagementController::class, 'create'])->name('sales.create')->middleware('can:Create-Sales');
            Route::post('/sales/store', [SalesManagementController::class, 'store'])->name('sales.store')->middleware('can:Create-Sales');
            Route::get('/sales/report', [SalesManagementController::class, 'report'])->name('sales.report')->middleware('can:View-Reports');
            Route::get('/sales/summary', [SalesManagementController::class, 'summary'])->name('sales.summary')->middleware('can:View-Reports');
            Route::delete('/sales/items/delete/{id}', [SalesManagementController::class, 'deleteItem'])->name('sales.items.delete')->middleware('can:Delete-Sales');
            Route::get('/admin/get-bank/{company_id}', [SalesManagementController::class, 'getBank']);
            // ================= CONTACTS =================
            Route::get('/contacts', [SalesManagementController::class, 'contacts'])->name('contacts.index')->middleware('can:View-Contacts');
            Route::post('/contacts', [SalesManagementController::class, 'storeContact'])->name('contacts.store')->middleware('can:Create-Contacts');
            Route::put('/contacts/{id}', [SalesManagementController::class, 'updateContact'])->name('contacts.update')->middleware('can:Edit-Contacts');
            Route::delete('/contacts/{id}', [SalesManagementController::class, 'deleteContact'])->name('contacts.delete')->middleware('can:Delete-Contacts');
            Route::post('/contacts/store',[SalesManagementController::class, 'storeContact'])->name('contacts.store')->middleware('can:Create-Contacts');
            Route::put('/contacts/update/{id}',[SalesManagementController::class, 'updateContact'])->name('contacts.update')->middleware('can:Edit-Contacts');
            // ================= CUSTOMERS =================
            Route::get('/customers', [SalesManagementController::class, 'customers'])->name('customers.index')->middleware('can:View-Customers');
            Route::post('/customers/store', [SalesManagementController::class, 'storeCustomer'])->name('sales.customers.store')->middleware('can:Create-Customers');
            Route::get('/customers/edit/{encryptedId}', [SalesManagementController::class, 'editCustomer'])->name('sales.customers.edit')->middleware('can:Edit-Customers');
            Route::put('/customers/update/{encryptedId}', [SalesManagementController::class, 'updateCustomer'])->name('sales.customers.update')->middleware('can:Edit-Customers');
            Route::delete('/customers/delete/{encryptedId}', [SalesManagementController::class, 'deleteCustomer'])->name('sales.customers.delete')->middleware('can:Delete-Customers');
            Route::get('/admin/customers/export/excel', [SalesManagementController::class, 'exportCustomersExcel'])->name('sales.customers.export.excel')->middleware('can:View-Customers');
            Route::get('/admin/customers/export/pdf', [SalesManagementController::class, 'exportCustomersPDF'])->name('sales.customers.export.pdf')->middleware('can:View-Customers');
            // ================= CUSTOMER AJAX =================
            Route::get('/ajax/company/{companyId}/business-units', [SalesManagementController::class, 'getBusinessUnitsByCompany'])->name('sales.ajax.business.units');
            Route::get('/ajax/business-unit/{unitId}/work-points', [SalesManagementController::class, 'getWorkPointsByBusinessUnit'])->name('sales.ajax.work.points');
            Route::get('/sales/customer-ledger',[SalesManagementController::class, 'customerLedger'])->name('sales.customer.ledger')->middleware(['auth','permission:View-Customer-Ledger']);
            Route::get('/sales/customer-ledger/export/excel',[SalesManagementController::class, 'exportCustomerLedgerExcel'])->name('sales.customer.ledger.export.excel')->middleware(['auth','permission:Export-Customer-Ledger']);
            Route::get('/sales/customer-ledger/print',[SalesManagementController::class, 'printCustomerLedger'])->name('sales.customer.ledger.print')->middleware([ 'auth','permission:Print-Customer-Ledger']);
            Route::post('/admin/invoice/from-proforma', [SalesManagementController::class, 'createInvoiceFromProforma'])->name('sales.invoice.from.proforma');
            // ================= AJAX DROPDOWNS (FIXED) =================
            Route::get('/get-business-units/{company_id}', [SalesManagementController::class, 'getBusiness'])->middleware('can:View-Companies');
            Route::get('/get-products/{workpoint}', [SalesManagementController::class, 'getProductsByWorkpoint']);
            Route::get('/admin/get-proforma-items/{id}',[ProformaController::class, 'getProformaItems']);
            Route::get('/get-work-points/{business_id}', [SalesManagementController::class, 'getWorkPoints'])->middleware('can:View-Companies');
            Route::get('/admin/get-proforma-items/{id}', [SalesManagementController::class, 'getProformaItems']);
            Route::put('/invoice/update/{id}', [SalesManagementController::class, 'updateInvoice'])->name('sales.invoice.update') ->middleware('permission:Edit-Invoice');
            Route::post('/invoice/update/{id}', [SalesManagementController::class, 'updateInvoice'])->name('sales.invoice.update.post')->middleware('permission:Edit-Invoice');
            // ================= ORDERS =================
            Route::get('/orders', [SalesManagementController::class, 'orders'])->name('sales.orders')->middleware('can:View-Sales');
            Route::post('/orders/store', [SalesManagementController::class, 'storeOrder'])->name('sales.orders.store')->middleware('can:Create-Sale');
            Route::get('/orders/{id}', [SalesManagementController::class, 'showOrder'])->name('sales.orders.show')->middleware('can:View-Sales');
            Route::get('/orders/edit/{id}', [SalesManagementController::class, 'editOrder'])->name('sales.orders.edit')->middleware('can:Edit-Sale');
            Route::post('/orders/update/{id}', [SalesManagementController::class, 'updateOrder'])->name('sales.orders.update')->middleware('can:Edit-Sale');
            Route::delete('/orders/delete/{id}', [SalesManagementController::class, 'deleteOrder'])->name('sales.orders.delete')->middleware('can:Delete-Sale');
            Route::get('/admin/orders/export', [SalesManagementController::class, 'exportOrders'])->name('sales.orders.export');
            Route::post('/admin/orders/update-notes/{id}', [SalesManagementController::class, 'updateOrderNotes'])->name('sales.orders.updateNotes');
            // ================= INVOICES =================
            Route::prefix('admin')->group(function () {
            Route::get('/invoices', [SalesManagementController::class, 'invoices'])->name('sales.invoices.index')->middleware('can:View-Invoices');
            Route::post('/invoice/store', [SalesManagementController::class, 'storeInvoice'])->name('sales.invoice.store')->middleware('can:Create-Invoices');
            Route::get('/invoice/excel/{id}', [SalesManagementController::class, 'exportInvoiceExcel'])->name('sales.invoice.excel');
            Route::get('/invoice/view/{id}', [SalesManagementController::class, 'viewInvoice'])->name('sales.invoice.view')->middleware('permission:View-Invoice');
            Route::get('/invoice/edit/{id}', [SalesManagementController::class, 'editInvoice'])->name('sales.invoice.edit')->middleware('permission:Edit-Invoice');
            Route::delete('/invoice/delete/{id}', [SalesManagementController::class, 'deleteInvoice'])->name('sales.invoice.delete');
            // PRINT (NO PERMISSION - OPEN)
            Route::get('/sales/invoice/print/{id}', [SalesManagementController::class, 'printInvoice'])->name('sales.invoice.print');});
             // ================= PAYMENTS =================
            Route::get('/payments',[SalesManagementController::class, 'payments'])->middleware('can:View-Payment') ->name('payments.index');
            Route::get('/sales/payments',[SalesManagementController::class, 'payments'])->middleware('can:View-Payment') ->name('sales.payments');
            Route::post('/payments/store',[SalesManagementController::class, 'storePayment'])->middleware('can:Create-Payment')->name('payments.store');
            Route::get('/payments/edit/{id}',)->middleware('can:Edit-Payment')->name('payments.edit');
            Route::put('/payments/update/{id}', [SalesManagementController::class,'updatePayment'])->middleware('can:Edit-Payment')->name('payments.update');
            Route::get('/payments/view/{id}', [SalesManagementController::class, 'viewPayment'])->middleware('can:View-Payment')->name('payments.view');
            Route::post('/payments/verify/{id}',[SalesManagementController::class, 'verifyPayment'])->middleware('can:Verify-Payment')->name('payments.verify');
            Route::delete('/payments/delete/{id}',[SalesManagementController::class, 'deletePayment'])->middleware('can:Delete-Payment')->name('payments.delete');
            Route::get('/payments/print/{id}', [SalesManagementController::class, 'printPayment'])->middleware('can:View-Payment') ->name('payments.print');
            Route::get('/payments/pdf/{id}',  [SalesManagementController::class, 'paymentPDF'])->middleware('can:View-Payment') ->name('payments.pdf');
            // ================= DERIVERIES =================
            Route::get('/admin/deliveries', [SalesManagementController::class, 'deliveries'])->middleware('can:View-Deliveries')->name('sales.deliveries');
            Route::post('/admin/deliveries/store',[SalesManagementController::class, 'storeDelivery'])->middleware('can:Create-Delivery')->name('sales.deliveries.store');
            Route::get('/get-order-items/{id}',[SalesManagementController::class, 'getOrderItems'])->name('get.order.items');
            Route::get('/delivery/approve/{id}',[SalesManagementController::class, 'approveDelivery'])->name('delivery.approve');
            Route::get('/delivery/pdf/{id}',[SalesManagementController::class, 'deliveryPDF'])->name('delivery.pdf');
            Route::get('/delivery/dispatch/{id}',[SalesManagementController::class, 'dispatchDelivery']) ->name('delivery.dispatch');
            Route::get('/delivery/confirm/{id}',[SalesManagementController::class, 'confirmDeliveryForm']) ->name('delivery.confirm.form');
            Route::post('/delivery/confirm/{id}',[SalesManagementController::class, 'confirmDelivery']) ->name('delivery.confirm');
            Route::get('/campaigns/delete/{id}',[SalesManagementController::class, 'deleteCampaign']) ->name('campaigns.delete')->middleware('can:Delete-Campaigns');
            // LEADS
            Route::get('/leads', [SalesManagementController::class, 'leads'])->name('sales.leads')->middleware('can:View-Leads');
            Route::post('/leads/store',[SalesManagementController::class, 'storeLead'])->name('sales.leads.store')->middleware('can:Create-Leads');
            Route::get('/leads/edit/{id}',[SalesManagementController::class, 'editLead'])->name('sales.leads.edit')->middleware('can:Edit-Leads');
            Route::post('/leads/update/{id}',[SalesManagementController::class, 'updateLead'])->name('sales.leads.update')->middleware('can:Edit-Leads');
            Route::delete('/leads/delete/{id}',[SalesManagementController::class, 'deleteLead'])->name('sales.leads.delete')->middleware('can:Delete-Leads');
            // FOLLOWUPS
            Route::get('/followups',[SalesManagementController::class, 'followups']) ->name('sales.followups')->middleware('can:View-Followups');
            Route::post('/followups/store',[SalesManagementController::class, 'storeFollowup'])->name('sales.followups.store');
            Route::post('/followups/store', [SalesManagementController::class, 'storeFollowup'])->name('sales.followups.store')->middleware('can:Create-Followups');
            Route::get('/followups/edit/{id}',[SalesManagementController::class, 'editFollowup'])->name('sales.followups.edit')->middleware('can:Edit-Followups');
            Route::post('/followups/update/{id}',[SalesManagementController::class, 'updateFollowup'])->name('sales.followups.update')->middleware('can:Edit-Followups');
            Route::delete('/followups/delete/{id}',[SalesManagementController::class, 'deleteFollowup'])->name('sales.followups.delete')->middleware('can:Delete-Followups');
            // COMMUNICATIONS
            Route::get('/communications',[SalesManagementController::class, 'communications'])->name('sales.communications')->middleware('can:View-Communications');
            Route::post('/communications/store', [SalesManagementController::class, 'storeCommunication'])->name('sales.communications.store')->middleware('can:Create-Communications');
            Route::get('/communications/edit/{id}',[SalesManagementController::class, 'editCommunication'])->name('sales.communications.edit')->middleware('can:Edit-Communications');
            Route::post('/communications/update/{id}',[SalesManagementController::class, 'updateCommunication'])->name('sales.communications.update')->middleware('can:Edit-Communications');
            Route::delete('/communications/delete/{id}',[SalesManagementController::class, 'deleteCommunication'])->name('sales.communications.delete') ->middleware('can:Delete-Communications');
            // SALES PIPELINE
            Route::get('/pipeline',[SalesManagementController::class, 'pipeline'])->name('sales.pipeline')->middleware('can:View-Pipelines');
            Route::post('/pipeline/store',[SalesManagementController::class, 'storePipeline'])->name('sales.pipeline.store') ->middleware('can:Create-Pipelines');
            Route::get('/pipeline/edit/{id}',[SalesManagementController::class, 'editPipeline'])->name('sales.pipeline.edit')->middleware('can:Edit-Pipelines');
            Route::post('/pipeline/update/{id}',[SalesManagementController::class, 'updatePipeline'])->name('sales.pipeline.update')->middleware('can:Edit-Pipelines');
            Route::delete('/pipeline/delete/{id}',[SalesManagementController::class, 'deletePipeline'])->name('sales.pipeline.delete')->middleware('can:Delete-Pipelines');
            // CUSTOMER LEDGER
            Route::get('/customer-ledger',[SalesManagementController::class, 'customerLedger'])->name('sales.customer.ledger')->middleware('can:View-Customer-Ledger');
            // CRM REPORTS
            Route::get('/reports',[SalesManagementController::class, 'crmReports'])->name('sales.crm.reports')->middleware('can:View-CRM-Reports');
            Route::get('/get-workpoints/{business_id}', [SalesManagementController::class, 'getWorkPoints']);
            Route::get('/get-customers/{work_point_id}', [SalesManagementController::class, 'getCustomers']);
            Route::get('/admin/get-banks/{company_id}', [SalesManagementController::class, 'getBanks']);
          // ================= DRILLING & BLASTING =================
            Route::prefix('production/drilling-blasting')->name('production.drilling-blasting.')->group(function () {
            Route::get('/',[DrillingBlastingController::class, 'index'])->name('index')->middleware('can:View-Drilling-Blasting');
            Route::post('/',[DrillingBlastingController::class, 'store'])->name('store')->middleware('can:Create-Drilling-Blasting');
            Route::get('/{id}',[DrillingBlastingController::class, 'show'])->name('show')->middleware('can:View-Drilling-Blasting');
            Route::post('/{id}/update',[DrillingBlastingController::class, 'update'])->name('update')->middleware('can:Edit-Drilling-Blasting');
            Route::delete('/{id}',[DrillingBlastingController::class, 'destroy'])->name('destroy')->middleware('can:Delete-Drilling-Blasting');
            // ================= AJAX =================
            Route::get('/ajax/company-units/{company_id}',[DrillingBlastingController::class, 'getCompanyUnits'])->name('ajax.company.units')->middleware('can:View-Drilling-Blasting');
            Route::get('/ajax/work-points/{company_id}/{unit_id}', [DrillingBlastingController::class, 'getWorkPoints']) ->name('ajax.work.points')->middleware('can:View-Drilling-Blasting');});
            // ================= PROFORMA =================
            Route::get('/admin/proformas', [ProformaController::class, 'index'])->name('sales.proformas')->middleware('can:View-Proforma');
            Route::post('/proforma/store', [ProformaController::class, 'store'])->name('proforma.store')->middleware('permission:Create-Proforma');
            Route::get('/proforma/edit/{id}', [ProformaController::class, 'edit'])->name('proforma.edit')->middleware('permission:Edit-Proforma');
            Route::post('/admin/proformas/store', [ProformaController::class, 'store'])->name('sales.proformas.store')->middleware('can:Create-Proforma');
            Route::put('/proforma/update/{id}', [ProformaController::class, 'update'])->name('proforma.update')->middleware('permission:Edit-Proforma');
            Route::post('/proforma/approve/{id}', [ProformaController::class, 'approve'])->name('proforma.approve')->middleware('permission:Approve-Proforma');
            Route::delete('/proforma/delete/{id}', [ProformaController::class, 'destroy'])->name('proforma.delete')->middleware('permission:Delete-Proforma');
            Route::get('/proforma/view/{id}', [ProformaController::class, 'view'])->name('proforma.view');
            Route::get('/proforma/pdf/{id}', [ProformaController::class, 'pdf'])->name('proforma.pdf');
            Route::get('/admin/proforma/print/{id}', [ProformaController::class, 'print'])->name('proforma.print'); 
            Route::get('/proformas', [ProformaController::class, 'index'])->name('proforma.index')->middleware('can:View-Proforma');
            Route::get('/admin/get-all-products', [ProformaController::class, 'getAllProducts'])->name('proforma.all.products');
            Route::get('/admin/get-all-services', [ProformaController::class, 'getAllServices'])->name('proforma.all.services');
            Route::get('/admin/get-business-units/{company_id}', [ProformaController::class, 'getBusinessUnits'])->name('get.business.units');
            Route::get('/admin/get-work-points/{business_id}', [ProformaController::class, 'getWorkPoints'])->name('get.work.points');
            //Invoice / Customer Payment / Delivery Routes  SALEINVOICE CONTROLLER
            Route::get('/admin/invoices', [SalesInvoiceController::class, 'index'])->name('sales.invoices.index')->middleware('permission:View-Invoices');
            Route::post('/admin/invoices/store', [SalesInvoiceController::class, 'store'])->name('sales.invoice.store')->middleware('permission:Register-Invoices');
            Route::get('/admin/invoices/view/{id}', [SalesInvoiceController::class, 'show'])->name('sales.invoice.view')->middleware('permission:View-Invoices');
            Route::get('/admin/invoices/print/{id}', [SalesInvoiceController::class, 'print'])->name('sales.invoice.print')->middleware('permission:View-Invoices');
            Route::get('/admin/invoices/edit/{id}', [SalesInvoiceController::class, 'edit'])->name('sales.invoice.edit')->middleware('permission:Edit-Invoices');
            Route::put('/admin/invoices/update/{id}', [SalesInvoiceController::class, 'update'])->name('sales.invoice.update')->middleware('permission:Edit-Invoices');
            Route::delete('/admin/invoices/delete/{id}', [SalesInvoiceController::class, 'destroy'])->name('sales.invoice.delete')->middleware('permission:Delete-Invoices');
            Route::post('/admin/invoices/payment/{id}', [SalesInvoiceController::class, 'recordAdditionalPayment'])->name('sales.invoice.payment')->middleware('permission:Create-Payments');
            Route::get('/admin/payments', [SalesInvoiceController::class, 'payments'])->name('sales.payments.index')->middleware('permission:View-Payments');
            Route::post('/admin/payments/verify/{id}', [SalesInvoiceController::class, 'verifyPayment'])->name('sales.payments.verify')->middleware('permission:Verify-Payments');
            // Payment print uses the same Tax Invoice print document.
            //The controller resolves payment -> invoice and redirects to sales.invoice.print.
            Route::get('/admin/payments/print/{id}', [SalesInvoiceController::class, 'printPayment'])->name('sales.payments.print')->middleware('permission:Print-Payments');
            Route::delete('/admin/payments/delete/{id}', [SalesInvoiceController::class, 'deletePayment'])->name('sales.payments.delete')->middleware('permission:Delete-Payments');
            Route::get('/admin/proformas/details/{id}', [SalesInvoiceController::class, 'getProformaDetails'])->name('sales.proforma.details')->middleware('permission:Register-Invoices');
            Route::get('/admin/invoice-products/{company_id}/{business_unit_id}/{workpoint_id}', [SalesInvoiceController::class, 'getProducts'])->name('sales.invoice.products')->middleware('permission:Register-Invoices');
            Route::get('/admin/invoice-services/{company_id}/{business_unit_id}/{workpoint_id}', [SalesInvoiceController::class, 'getServices'])->name('sales.invoice.services')->middleware('permission:Register-Invoices');
            Route::get('/admin/payment-accounts/{method}', [SalesInvoiceController::class, 'getPaymentAccounts'])->name('sales.payment.accounts')->middleware('permission:Create-Payments|Approve-Delivery|Register-Invoices');
            // DELIVERY CRUD + APPROVAL
            Route::get('/admin/deliveries', [SalesInvoiceController::class, 'deliveries'])->name('sales.deliveries')->middleware('permission:View-Delivery');
            Route::post('/admin/deliveries/store', [SalesInvoiceController::class, 'storeDelivery'])->name('sales.deliveries.store')->middleware('permission:Create-Delivery');
            Route::get('/admin/deliveries/edit/{id}', [SalesInvoiceController::class, 'editDelivery'])->name('sales.deliveries.edit')->middleware('permission:Edit-Delivery');
            Route::put('/admin/deliveries/update/{id}', [SalesInvoiceController::class, 'updateDelivery'])->name('sales.deliveries.update')->middleware('permission:Edit-Delivery');
            Route::delete('/admin/deliveries/delete/{id}', [SalesInvoiceController::class, 'deleteDelivery'])->name('sales.deliveries.delete')->middleware('permission:Delete-Delivery');
            Route::post('/admin/deliveries/approve/{id}', [SalesInvoiceController::class, 'approveDelivery'])->name('sales.deliveries.approve')->middleware('permission:Approve-Delivery');
            Route::post('/admin/deliveries/accept/{id}', [SalesInvoiceController::class, 'acceptDelivery'])->name('sales.deliveries.accept')->middleware('permission:Approve-Delivery');
            Route::get('/admin/deliveries/note/{id}', [SalesInvoiceController::class, 'deliveryNote'])->name('sales.delivery.note')->middleware('permission:View-Delivery');
            Route::get('/admin/deliveries/waybill/{id}', [SalesInvoiceController::class, 'waybill'])->name('sales.delivery.waybill')->middleware('permission:View-Delivery');
            Route::get('/sales/dashboard', [SalesManagementController::class, 'dashboard'])->name('sales.dashboard');
            Route::get('/admin/ajax/company-units/{company_id}', [SalesInvoiceController::class, 'ajaxCompanyUnits'])->name('sales.ajax.company.units');
            Route::get('/admin/ajax/unit-workpoints/{business_unit_id}', [SalesInvoiceController::class, 'ajaxUnitWorkPoints'])->name('sales.ajax.unit.workpoints');
            Route::get('/admin/ajax/delivery-invoice/{id}', [SalesInvoiceController::class, 'ajaxDeliveryInvoice'])->name('sales.ajax.delivery.invoice');
            Route::get('/admin/ajax/delivery-proforma/{id}', [SalesInvoiceController::class, 'ajaxDeliveryProforma'])->name('sales.ajax.delivery.proforma');
            Route::get('/admin/deliveries/packing-list/{id}', [SalesInvoiceController::class, 'packingList'])->name('sales.delivery.packing.list')->middleware('permission:View-Delivery');
            Route::get('/admin/deliveries/customs-road-manifest/{id}', [SalesInvoiceController::class, 'customsRoadManifest'])->name('sales.delivery.customs.manifest')->middleware('permission:View-Delivery');

            // POS (API style)
            Route::get('/api/business-units/{company_id}', [PosSalesManagementController::class, 'getBusinessUnits']);
            Route::get('/api/workpoints/{business_unit_id}', [PosSalesManagementController::class, 'getWorkPoints']);
            Route::get('/admin/get-business-units/{company_id}', function ($company_id) {return \App\Models\Company_unit::where('company_id', $company_id)->get();});
            // ERP (blade yako ya customer)
            Route::get('/sales-management/pos-sales', [PosSalesManagementController::class, 'pos'])->name('sales.pos');
            Route::get('/cart', [PosSalesManagementController::class,'getCart']);
            Route::post('/cart/add', [PosSalesManagementController::class,'addToCart']);
            Route::post('/cart/remove', [PosSalesManagementController::class,'removeFromCart']);
            Route::post('/pos/checkout', [PosSalesManagementController::class,'checkout']);
            Route::get('/pos/receipt/{id}', [PosSalesManagementController::class,'receipt'])->name('pos.receipt');
            Route::get('/pos/search', [PosSalesManagementController::class, 'search']);});
            
            //logistics
            Route::prefix('logistics')->name('logistics.')->middleware(['auth'])->group(function () {
            Route::get('/dashboard', [LogisticsController::class, 'dashboard'])->name('dashboard')->middleware('can:View-Logistics-Menu');

            Route::get('/orders', [LogisticsController::class, 'ordersIndex'])->name('orders')->middleware('can:View-Transport-Orders');
            Route::get('/orders/create', [LogisticsController::class, 'ordersCreate'])->name('orders.create')->middleware('can:Create-Transport-Orders');
            Route::post('/orders', [LogisticsController::class, 'ordersStore'])->name('orders.store')->middleware('can:Create-Transport-Orders');
            Route::get('/orders/{id}', [LogisticsController::class, 'ordersShow'])->name('orders.show')->middleware('can:View-Transport-Orders');
            Route::get('/orders/{id}/edit', [LogisticsController::class, 'ordersEdit'])->name('orders.edit')->middleware('can:Edit-Transport-Orders');
            Route::put('/orders/{id}', [LogisticsController::class, 'ordersUpdate'])->name('orders.update')->middleware('can:Edit-Transport-Orders');
            Route::delete('/orders/{id}', [LogisticsController::class, 'ordersDestroy'])->name('orders.destroy')->middleware('can:Delete-Transport-Orders');

            Route::get('/fleet', [LogisticsController::class, 'fleetIndex'])->name('fleet')->middleware('can:View-Fleet-Management');
            Route::post('/fleet/vehicles', [LogisticsController::class, 'fleetVehicleStore'])->name('fleet.vehicles.store')->middleware('can:Create-Fleet-Management');
            Route::put('/fleet/vehicles/{id}', [LogisticsController::class, 'fleetVehicleUpdate'])->name('fleet.vehicles.update')->middleware('can:Edit-Fleet-Management');
            Route::delete('/fleet/vehicles/{id}', [LogisticsController::class, 'fleetVehicleDestroy'])->name('fleet.vehicles.destroy')->middleware('can:Delete-Fleet-Management');

            Route::post('/fleet/drivers', [LogisticsController::class, 'driverStore'])->name('fleet.drivers.store')->middleware('can:Create-Fleet-Management');
            Route::put('/fleet/drivers/{id}', [LogisticsController::class, 'driverUpdate'])->name('fleet.drivers.update')->middleware('can:Edit-Fleet-Management');
            Route::delete('/fleet/drivers/{id}', [LogisticsController::class, 'driverDestroy'])->name('fleet.drivers.destroy')->middleware('can:Delete-Fleet-Management');

            Route::post('/fleet/escorts', [LogisticsController::class, 'escortStore'])->name('fleet.escorts.store')->middleware('can:Create-Fleet-Management');
            Route::put('/fleet/escorts/{id}', [LogisticsController::class, 'escortUpdate'])->name('fleet.escorts.update')->middleware('can:Edit-Fleet-Management');
            Route::delete('/fleet/escorts/{id}', [LogisticsController::class, 'escortDestroy'])->name('fleet.escorts.destroy')->middleware('can:Delete-Fleet-Management');

            Route::get('/costing', [LogisticsController::class, 'costingIndex'])->name('costing')->middleware('can:View-Transport-Costing');
            Route::post('/costing/{orderId}/recalculate', [LogisticsController::class, 'costingRecalculate'])->name('costing.recalculate')->middleware('can:Edit-Transport-Costing');

            Route::get('/ajax/company-units/{company_id}', [LogisticsController::class, 'getCompanyUnits'])->name('ajax.company.units');
            Route::get('/ajax/work-points/{company_id}/{unit_id}', [LogisticsController::class, 'getWorkPoints'])->name('ajax.work.points');
            }); 
            });
            });