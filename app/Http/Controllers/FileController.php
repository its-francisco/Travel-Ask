<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FileController extends Controller
{
    static string $default = 'default.png';
    static string $diskName = 'Travel&Ask';

    static array $systemTypes = [
        'profile' => ['png', 'jpg', 'jpeg']
    ];

    private static function isValidType(String $type) {
        return array_key_exists($type, self::$systemTypes);
    }

    private static function defaultAsset(String $type) {
        return asset($type . '/' . self::$default);
    }

    private static function getFileName (String $type, int $id) {

        $fileName = null;
        switch($type) {
            case 'profile':
                $user = User::find($id);
                if($user && isset($user->photo)) {
                    $fileName = $user->photo;
                }
                break;
        }

        return $fileName;
    }

    static function get(String $type, int $userId) {

        // Validation: upload type
        if (!self::isValidType($type)) {
            return self::defaultAsset($type);
        }

        // Validation: file exists
        $fileName = self::getFileName($type, $userId);
        if ($fileName) {
            return asset($type . '/' . $fileName);
        }

        // Not found: returns default asset
        return self::defaultAsset($type);
    }

    public function store(Request $request) {
        $fileName = "";
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = \Str::uuid()->toString();
            $file->storeAs('posts', $fileName, FileController::$diskName);
        }
        return response()->json($fileName);
    }

}
