<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\LiveDisplayController;
use App\Http\Controllers\IdentityReaderController;
use App\Http\Controllers\PickupPersonnelController;
use App\Http\Controllers\StudentIdentityController;
use App\Http\Controllers\VehicleIdentityController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\StudentVehicleMappingController;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\LiveAdminController;
use App\Http\Controllers\StudentPickupPersonnelMappingController;


Route::get('/', function () {
//    return view('welcome');
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin
    Route::get('/admin', [RegisteredUserController::class, 'index'])->name('admin.index');
    Route::get('/admin/search', [RegisteredUserController::class, 'search'])->name('admin.search');
    Route::put('/admin/approve/{user:id}', [RegisteredUserController::class, 'approve'])->name('admin.approve');
    Route::delete('/admin/delete/{user:id}', [RegisteredUserController::class, 'delete'])->name('admin.delete');

    // Siswa
    Route::get('/siswa', [StudentController::class, 'index'])->name('siswa');
    Route::get('/siswa/search', [StudentController::class, 'search'])->name('siswa.search');
    Route::get('/siswa/assign/{student:id}', [StudentController::class, 'assign'])->name('student.assign');
    Route::put('/siswa/associate/{student:id}', [StudentController::class, 'associate'])->name('student.associate');
    Route::get('/siswa/edit_associate/{student_identity:id}', [StudentController::class, 'edit_associate'])->name('student.edit_associate');
    Route::put('/siswa/update_associate/{student_identity:id}', [StudentController::class, 'update_associate'])->name('student.update_associate');
    Route::put('/siswa/delete_associate/{student_identity:id}', [StudentController::class, 'disassociate'])->name('student.delete_associate');
    Route::get('/siswa/filter', [StudentController::class, 'filter'])->name('student.filter');
    Route::post('/siswa/import',  [StudentController::class, 'import'])->name('student.import');
    Route::get('/siswa/create', [StudentController::class, 'create'])->name('student.create');
    Route::post('/siswa/insert', [StudentController::class, 'insert'])->name('student.insert');
    Route::get('/siswa/edit/{student:id}', [StudentController::class, 'edit'])->name('student.edit');
    Route::put('/siswa/update/{student:id}', [StudentController::class, 'update'])->name('student.update');
    Route::delete('/siswa/delete/{student:id}', [StudentController::class, 'delete'])->name('student.delete');

    //Tag Siswa
    Route::get('/tag_siswa', [StudentIdentityController::class, 'index'])->name('tag_siswa');
    Route::get('/tag_siswa/search', [StudentIdentityController::class, 'search'])->name('tag_siswa.search');
    Route::get('/tag_siswa/create', [StudentIdentityController::class, 'create'])->name('student-identity.create');
    Route::post('/tag_siswa/insert', [StudentIdentityController::class, 'insert'])->name('student-identity.insert');
    Route::get('/tag_siswa/edit/{student_identity:id}', [StudentIdentityController::class, 'edit'])->name('student-identity.edit');
    Route::put('/tag_siswa/update/{student_identity:id}', [StudentIdentityController::class, 'update'])->name('student-identity.update');
    Route::delete('/tag_siswa/delete/{student_identity:id}', [StudentIdentityController::class, 'delete'])->name('student-identity.delete');
    Route::post('/tag_siswa/import', [StudentIdentityController::class, 'import'])->name('student-identity.import');


    //Penjemput
    Route::get('/penjemput', [PickupPersonnelController::class, 'index'])->name('pickup-personnel.index');
    Route::get('/penjemput/search', [PickupPersonnelController::class, 'search']);
    Route::get('/penjemput/create', [PickupPersonnelController::class, 'create'])->name('pickup-personnel.create');
    Route::post('/penjemput/insert', [PickupPersonnelController::class, 'insert'])->name('pickup-personnel.insert');
    Route::get('/penjemput/edit/{pickup_personnel:id}', [PickupPersonnelController::class, 'edit'])->name('pickup-personnel.edit');
    Route::put('/penjemput/update/{pickup_personnel:id}', [PickupPersonnelController::class, 'update'])->name('pickup-personnel.update');
    Route::delete('/penjemput/delete/{pickup_personnel:id}', [PickupPersonnelController::class, 'delete'])->name('pickup-personnel.delete');
    Route::get('/penjemput/assign-siswa/{pickup_personnel:id}', [StudentPickupPersonnelMappingController::class, 'create'])->name('pickup-personnel.assign-siswa');
    Route::get('/penjemput/edit_associate-siswa/{student:id}/{pickup_personnel:id}', [PickupPersonnelController::class, 'edit_associate_siswa'])->name('pickup-personnel.edit_associate-siswa');
    Route::delete('/penjemput/delete_associate-siswa/{student:id}/{pickup_personnel:id}', [PickupPersonnelController::class, 'disassociate_siswa'])->name('pickup-personnel.delete_associate-siswa');
    Route::post('penjemput/import', [PickupPersonnelController::class, 'import'])->name('pickup-personnel.import');
    Route::put('penjemput/toggle-notif/{pickupPersonnel:id}', [PickupPersonnelController::class, 'toggleNotif'])->name('pickup-personnel.toggle-notif');
    //  Route::get('/tag_siswa/search', [VehicleIdentityController::class, 'search'])->name('tag_siswa.search');
    //  Route::get('/tag_siswa/create', [VehicleIdentityController::class, 'create'])->name('student-identity.create');
    //  Route::post('/tag_siswa/insert', [VehicleIdentityController::class, 'insert'])->name('student-identity.insert');
    //  Route::get('/tag_siswa/edit/{student_identity:id}', [VehicleIdentityController::class, 'edit'])->name('student-identity.edit');
    //  Route::put('/tag_siswa/update/{student_identity:id}', [VehicleIdentityController::class, 'update'])->name('student-identity.update');
    //  Route::delete('/tag_siswa/delete/{student_identity:id}', [VehicleIdentityController::class, 'delete'])->name('student-identity.delete');

    // Student Pickup Personnel Mapping
    // Route::get('/student-pickup-personnel-mapping', [StudentPickupPersonnelMappingController::class, 'index'])->name('student-pickup-personnel-mapping.index');;
    Route::get('/student-pickup-personnel-mapping/search', [StudentPickupPersonnelMappingController::class, 'search']);
    Route::get('/student-pickup-personnel-mapping/create', [StudentPickupPersonnelMappingController::class, 'create'])->name('student-pickup-personnel-mapping.create');
    Route::post('/student-pickup-personnel-mapping/insert/{pickup_personnel:id}', [StudentPickupPersonnelMappingController::class, 'insert'])->name('student-pickup-personnel-mapping.insert');
    Route::get('/student-pickup-personnel-mapping/edit/{student_pickup_personnel_mapping:id}', [StudentPickupPersonnelMappingController::class, 'edit'])->name('student-pickup-personnel-mapping.edit');
    Route::put('/student-pickup-personnel-mapping/update/{student_pickup_personnel_mapping:id}', [StudentPickupPersonnelMappingController::class, 'update'])->name('student-pickup-personnel-mapping.update');
    Route::delete('/student-pickup-personnel-mapping/delete/{student_pickup_personnel_mapping:id}', [StudentPickupPersonnelMappingController::class, 'delete'])->name('student-pickup-personnel-mapping.delete');

    // Tag Kendaraan
    Route::get('/tag_kendaraan', [VehicleIdentityController::class, 'index'])->name('vehicle-identity.index');
    Route::get('/tag_kendaraan/search', [VehicleIdentityController::class, 'search']);
    Route::get('/tag_kendaraan/create', [VehicleIdentityController::class, 'create'])->name('vehicle-identity.create');
    Route::post('/tag_kendaraan/insert', [VehicleIdentityController::class, 'insert'])->name('vehicle-identity.insert');
    Route::get('/tag_kendaraan/edit/{vehicle_identity:id}', [VehicleIdentityController::class, 'edit'])->name('vehicle-identity.edit');
    Route::put('/tag_kendaraan/update/{vehicle_identity:id}', [VehicleIdentityController::class, 'update'])->name('vehicle-identity.update');
    Route::delete('/tag_kendaraan/delete/{vehicle_identity:id}', [VehicleIdentityController::class, 'delete'])->name('vehicle-identity.delete');
    Route::post('/tag_kendaraan/import', [VehicleIdentityController::class, 'import'])->name('vehicle-identity.import');


    // Kendaraan
    Route::get('/kendaraan', [VehicleController::class, 'index'])->name('vehicle.index');;
    Route::get('/kendaraan/search', [VehicleController::class, 'search']);
    Route::get('/kendaraan/create', [VehicleController::class, 'create'])->name('vehicle.create');
    Route::post('/kendaraan/insert', [VehicleController::class, 'insert'])->name('vehicle.insert');
    Route::get('/kendaraan/edit/{vehicle:id}', [VehicleController::class, 'edit'])->name('vehicle.edit');
    Route::put('/kendaraan/update/{vehicle:id}', [VehicleController::class, 'update'])->name('vehicle.update');
    Route::delete('/kendaraan/delete/{vehicle:id}', [VehicleController::class, 'delete'])->name('vehicle.delete');
    Route::get('/kendaraan/assign/{vehicle:id}', [VehicleController::class, 'assign'])->name('vehicle.assign');
    Route::put('/kendaraan/associate/{vehicle:id}', [VehicleController::class, 'associate'])->name('vehicle.associate');
    Route::get('/kendaraan/edit_associate/{vehicle_identity:id}', [VehicleController::class, 'edit_associate'])->name('vehicle.edit_associate');
    Route::put('/kendaraan/update_associate/{vehicle_identity:id}', [VehicleController::class, 'update_associate'])->name('vehicle.update_associate');
    Route::put('/kendaraan/delete_associate/{vehicle_identity:id}', [VehicleController::class, 'disassociate'])->name('vehicle.delete_associate');
    Route::get('/kendaraan/assign-siswa/{vehicle:id}', [StudentVehicleMappingController::class, 'create'])->name('vehicle.assign-siswa');
    Route::get('/kendaraan/edit_associate-siswa/{student:id}/{vehicle:id}', [VehicleController::class, 'edit_associate_siswa'])->name('vehicle.edit_associate-siswa');
    Route::delete('/kendaraan/delete_associate-siswa/{student:id}/{vehicle:id}', [VehicleController::class, 'disassociate_siswa'])->name('vehicle.delete_associate-siswa');
    Route::post('/kendaraan/import', [VehicleController::class, 'import'])->name('vehicle.import');

    // Student Vehicle Mapping
    Route::get('/student-vehicle-mapping', [StudentVehicleMappingController::class, 'index'])->name('student-vehicle-mapping.index');;
    Route::get('/student-vehicle-mapping/search', [StudentVehicleMappingController::class, 'search']);
    Route::get('/student-vehicle-mapping/create', [StudentVehicleMappingController::class, 'create'])->name('student-vehicle-mapping.create');
    Route::post('/student-vehicle-mapping/insert/{vehicle:id}', [StudentVehicleMappingController::class, 'insert'])->name('student-vehicle-mapping.insert');
    Route::get('/student-vehicle-mapping/edit/{student_vehicle_mapping:id}', [StudentVehicleMappingController::class, 'edit'])->name('student-vehicle-mapping.edit');
    Route::put('/student-vehicle-mapping/update/{student_vehicle_mapping:id}', [StudentVehicleMappingController::class, 'update'])->name('student-vehicle-mapping.update');
    Route::delete('/student-vehicle-mapping/delete/{student_vehicle_mapping:id}', [StudentVehicleMappingController::class, 'delete'])->name('student-vehicle-mapping.delete');

    // Live Display
    Route::get('/live-display', [LiveDisplayController::class, 'index'])->name("live-display.index");
    Route::get('/live-display/search', [LiveDisplayController::class, 'search'])->name('live-display.search');
    Route::get('/live-display/create', [LiveDisplayController::class, 'create'])->name("live-display.create");
    Route::get('/live-display/edit/{live_display:id}', [LiveDisplayController::class, 'editor'])->name("live-display.edit");
    Route::post('/live-display/insert/{liveDisplay}', [LiveDisplayController::class, 'insert'])->name("live-display.insert");
    Route::put('/live-display/update/{live_display:id}', [LiveDisplayController::class, 'update'])->name("live-display.update");
    Route::delete('/live-display/delete/{live_display:id}', [LiveDisplayController::class, 'delete'])->name("live-display.delete");
    Route::put('/live-display/enable/{liveDisplay:id}', [LiveDisplayController::class, 'enable'])->name('live-display.enable');
    Route::put('/live-display/disable/{liveDisplay:id}', [LiveDisplayController::class, 'disable'])->name('live-display.disable');



    // Identity Reader
    Route::get('/identity-reader', [IdentityReaderController::class, 'index'])->name("identity-reader.index");
    Route::post('/identity-reader/create', [IdentityReaderController::class, 'add'])->name("identity-reader.create");
    Route::post('/identity-reader/configure', [IdentityReaderController::class, 'configure'])->name("identity-reader.configure");
    Route::put('/identity-reader/{identityReader:id}', [IdentityReaderController::class, 'update'])->name("identity-reader.update");
    Route::delete('/identity-reader/{identityReader:id}', [IdentityReaderController::class, 'delete'])->name("identity-reader.delete");

    // History
    Route::get('/arrival-log', [HistoryController::class, 'index_arrival'])->name("arrival-log.index");
    Route::get('/arrival-log/search', [HistoryController::class, 'search_arrival'])->name("arrival-log.search");
    Route::get('/departure-log', [HistoryController::class, 'index_departure'])->name("departure-log.index");
    Route::get('/departure-log/search', [HistoryController::class, 'search_departure'])->name("departure-log.search");
    Route::get('/arrival-departure-tracking', [HistoryController::class, 'index_tracking'])->name("tracking.index");
    Route::get('/arrival-departure-tracking/search', [HistoryController::class, 'search_tracking'])->name("tracking.search");
});

Route::get('/live', function () {
    return Inertia::render('LiveView');
});

Route::get('/live-admin', function () {
    return Inertia::render('LiveViewAdmin');
})->name('live-admin.index');

Route::get('/license-plates', [LiveAdminController::class, 'getLicensePlate'])->name('license-plates.getLicensePlate');
Route::get('/reader-status', [LiveAdminController::class, 'statusReader']); // Temporary
Route::post('/get-new-entry', [LiveAdminController::class, 'getNewEntry']);
Route::post('/mark-departed', [LiveAdminController::class, 'markDeparted']);
Route::post('/change-order', [LiveAdminController::class, 'changeOrder']);

require __DIR__ . '/auth.php';
