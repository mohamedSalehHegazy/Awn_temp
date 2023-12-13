<?php

use App\Models\ExceptionLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

if (!function_exists('apiResponse')) {
    /**
     * Unified Api Response
     * @param $success
     * @param $message
     * @param $statusCode
     * @param null $data
     * @return json
     */
    function apiResponse($success, $message, $statusCode, $data = null, $paginationData = null)
    {
        $response =  [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];

        if($paginationData){
            $response['pagination_data'] = $paginationData;
        }

        return response()->json($response, $statusCode);
    }
}

if (!function_exists('failResponse')) {
    /**
     * FailResponse
     * @param $error
     * @return json
     */
    function failResponse($error)
    {
        Log::error($error);
        $ip = request()->ip();
        $ipInfo = file_get_contents("http://ip-api.com/json/" . $ip);
        $ipInfo = json_decode($ipInfo);
        ExceptionLog::create([
            'file'          =>  $error->getFile(),
            'url'           =>  request()->getPathInfo(),
            'message'       =>  $error->getMessage(),
            'user_ip'       =>  request()->ip(),
            'app_type'      =>  str_contains(request()->path(), 'admin') == true ? 'AdminPanel' : 'Portal',
            'country'       =>  isset($ipInfo->country) == true ? $ipInfo->country :  '',
            'city'          =>  isset($ipInfo->city) == true ? $ipInfo->city : '',
            'data'          =>  request()->all(),
        ]);
        $response =  [
            'success' => false,
            'message' => trans('messages.some_thing_wrong'),
            'data' => '',
        ];

        return response()->json($response, 500);
    }
}



if (!function_exists('uploadFile')) {
    /**
     * upload File in specific directory "storage"
     * @param $upload
     * @param $path
     * @param null $resize_width
     * @param null $resize_height
     * @return string
     */
    function uploadFile($file, $path): string
    {
        $checkPath = 'app/public/' . $path;
        if (!file_exists(storage_path($checkPath))) {
            mkdir(storage_path($checkPath), 777, true);
        }
        $fileName   = time() . $file->getClientOriginalName();
        Storage::disk('public')->put($path . '/' . $fileName, File::get($file));
        return $fileName;
    }
}

if (!function_exists('deleteFile')) {
    /**
     * delete File
     * @param $path
     * @return int
     */
    function deleteFile($file, $folder): int
    {
        $filePath = explode('/',$file);
        $file = end($filePath);
        $fullPath = $folder . '/' . $file;
        
        if (Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->delete($fullPath);
        } else {
            return (7);
        }
        return true;
    }
}

if (!function_exists('canPass')) {
    /**
     * check if user has permission or not
     * @param
     * @return bool
     */
    function canPass()
    {
        $user = auth('admins')->user();

        if ($user == null) {
            return 401;
        }

        $userPermissions =  $user->roles->first()->permissions->pluck('url')->toArray();
        $currentRoute = request()->route()->getName();
        if (in_array($currentRoute, $userPermissions)) {
            return 200;
        }
        return 403;
    }
}


if (!function_exists('sendSms')) {
    /**
     * Send SMS
     * @param $phone
     */
    function sendSms($phone, $message = null)
    {
        // code ..
        return [
            'success' => true,
            'otp' => rand(0000, 9999)
        ];
    }
}
