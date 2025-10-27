<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function downloadDatabase()
    {
        $database   = env('DB_DATABASE');
        $username   = env('DB_USERNAME');
        $password   = env('DB_PASSWORD');
        $host       = env('DB_HOST');
        $port       = env('DB_PORT', 3306);

        $fileName = 'backup_' . date('y-m-d') . '.sql';
        $filePath = storage_path($fileName);

        $command = sprintf(
            'mysqldump -h %s -P %s -u %s --password="%s" %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password,
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        $result = null;
        $output = null;
        exec($command, $output, $result);

        if ($result !== 0 || !file_exists($filePath)) {
            return back()->with('error', 'Database backup failed! Please check server permissions or mysqldump path.');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
