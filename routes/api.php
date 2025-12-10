<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\PictureAttemptController;
use App\Http\Controllers\Api\PictureController;
use App\Http\Controllers\Api\ReadingAttemptController;
use App\Http\Controllers\Api\ReadingExerciseController;
use App\Http\Controllers\Api\SpellingActivityController as ApiSpellingActivityController;
use App\Http\Controllers\Api\SpellingAttemptController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\SpellingController;
use App\Http\Controllers\Api\TeacherController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/forgot-password', [UserManagementController::class, 'resetPassword']);
//
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [UserManagementController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refreshToken']);

    //Student Managemnt
    Route::get('/student/{id}', [UserManagementController::class, 'getStudentById']);
    Route::post('/student/{id}', [UserManagementController::class, 'updateProfileById']);

    //Lesson Route
    Route::get('/student/{id}/teacher', [LessonController::class, 'get_teacherId']);
    Route::get('/lesson/{lesson_id}/activities', [LessonController::class, 'getLessonActivities']);

    //spelling attempt
    Route::post('/spelling-attempts', [SpellingAttemptController::class, 'store']);
    Route::get('/spelling-attempts/user/{user_id}', [SpellingAttemptController::class, 'getByUser']);

    //picture attempt
     Route::post('/picture-attempts', [PictureAttemptController::class, 'store']);

    //reading attempt
    Route::post('/reading-attempts', [ReadingAttemptController::class, 'store']);

    // Only admin & dev can manage users
    Route::middleware(['role:administrator,developer,teacher'])->group(function () {
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::get('/users/not-my-students', [UserManagementController::class, 'notMyStudent']);
        Route::post('/users/assign', [UserManagementController::class, 'assignStudents']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::put('/users/{id}/role', [UserManagementController::class, 'updateRole']);

        //reset student password
        Route::post('student/{id}/reset-password', [TeacherController::class, 'resetStudentPassword']);

        //Teachers
        Route::get('/teachers', [TeacherController::class, 'getTeachers']);
        Route::post('/teachers', [TeacherController::class, 'storeTeacher']);
        Route::post('/teacher/{id}', [TeacherController::class, 'updateProfileById']);
        Route::delete('/teachers/{id}', [TeacherController::class, 'deleteTeacher']);

        //Spelling Routes
        Route::post('/spelling', [SpellingController::class, 'saveLevels']);
        Route::get('/spelling/levels', [SpellingController::class, 'getLevels']);

        //Lesson Routes
        Route::get('/lessons', [LessonController::class, 'index']); // fetch lessons
        Route::post('/lesson', [LessonController::class, 'store']);

         //lesson and activities screen
        Route::get('/teacher/{id}/lessons', [LessonController::class, 'lesson_and_student_records']);

        //home screen for teacher
        Route::get('/teacher/{id}/stats', [LessonController::class, 'home_screen']);

        //report screen for teacher
        Route::get('/teacher/report/{id}/{type}', [LessonController::class, 'report_screen']);

        //Reading Exercise Routes
        Route::get('/lessons/{id}/exercises', [ReadingExerciseController::class, 'index']);
        Route::post('/exercises', [ReadingExerciseController::class, 'store']);
        Route::delete('/exercises/{id}', [ReadingExerciseController::class, 'destroy']);
        Route::put('/exercises/{exercise}', [ReadingExerciseController::class, 'update']);

        // Picture guessing
        Route::get('/pictures', [PictureController::class, 'index']); // require ?lesson_id=...
        Route::post('/pictures', [PictureController::class, 'store']);
        Route::put('/pictures/{picture}', [PictureController::class, 'update']);
        Route::delete('/pictures/{picture}', [PictureController::class, 'destroy']);

        // Spelling Routes
        Route::get('/spelling-activities', [ApiSpellingActivityController::class, 'index']);
        Route::post('/spelling-activities', [ApiSpellingActivityController::class, 'store']);
        Route::put('/spelling-activities/{id}', [ApiSpellingActivityController::class, 'update']);
        Route::delete('/spelling-activities/{id}', [ApiSpellingActivityController::class, 'destroy']);



    });
});


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
