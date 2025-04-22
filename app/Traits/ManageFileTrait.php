<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use function App\apiResponse;

trait ManageFileTrait
{
    public function uploadFile(Request $request, $fileName, $folderName)
    {
        if ($request->hasfile($fileName) && $request->$fileName != Null) {
            $path = $request->file($fileName)->store($folderName, 'public');
            return $path;
        }
        return Null;
    }

    public function getFileUrl($path)
    {
        if (!$path)
            return null;

        return asset('storage/' . $path);
    }

    public function deleteFile($path)
    {
        if (file_exists(storage_path($path))) {
            return File::delete(storage_path($path));
        }
        return apiResponse(401, '', "File doesn't exists");
    }
}
