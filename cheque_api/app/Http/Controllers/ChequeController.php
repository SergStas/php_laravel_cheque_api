<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\Partition;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;

class ChequeController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:cheques,name',
            'file' => 'required|file',
        ]);
        $cheque = Cheque::create([
            'name' => $validated['name'],
        ]);
        $file = $cheque->addMediaFromRequest('file')
            ->toMediaCollection('imports');
        $filename = storage_path('app\\public\\'.$file->id.'\\'.$file->file_name);
        $is_content_row = FALSE;
        $row = 0;
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($is_content_row == TRUE) {
                    $row++;
                    $user_names = preg_split("~,~", $data[2]);
                    $position = Position::create([
                        'name' => $data[0],
                        'sum' => $data[1],
                        'cheque_id' => $cheque->id,
                    ]);
                    foreach ($user_names as $user_name)
                    {
                        $user = User::where('name', $user_name)->first();
                        if (!$user)
                        {
                            return response([
                                'status' => 'error',
                                'message' => 'User '.$user_name.' is absent',
                            ]);
                        }
                        Partition::create([
                            'user_id' => $user->id,
                            'position_id' => $position->id,
                        ]);
                    }
                }
                $is_content_row = TRUE;
            }
            fclose($handle);
        }

        return response(['status' => 'success'], 201);
    }
}
