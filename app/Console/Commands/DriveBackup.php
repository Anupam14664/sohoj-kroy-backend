<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class DriveBackup extends Command
{
    protected $signature = 'backup:drive';
    protected $description = 'Backup project and upload to Google Drive';

    public function handle()
    {
        $this->info('Starting backup...');

        // 1️⃣ Run local backup (Laravel backup package)
        $this->call('backup:run');

        $this->info('Searching for latest backup ZIP...');

        // 2️⃣ Find latest backup zip explicitly in private/Laravel folder
        $backupPath = collect(glob(storage_path('app/private/Laravel/*.zip')))
            ->sortDesc()
            ->first();

        if (!$backupPath) {
            $this->error('No backup file found in storage/app/private/Laravel/');
            return;
        }

        $this->info('Found backup file: ' . $backupPath);

        // 3️⃣ Setup Google Client
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/edulife-backup.json')); // path to your service account JSON
        $client->addScope(Drive::DRIVE);

        $drive = new Drive($client);
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

        if (!$folderId) {
            $this->error('GOOGLE_DRIVE_FOLDER_ID not set in .env');
            return;
        }

        $this->info('Deleting old backups from Google Drive...');

        // 4️⃣ Delete old backups in Shared Drive folder
        try {
            $files = $drive->files->listFiles([
                'q' => "'{$folderId}' in parents and trashed=false",
                'fields' => 'files(id, name)',
            ]);

            foreach ($files->files as $file) {
                $this->info('Deleting old file: ' . $file->name);
                $drive->files->delete($file->id);
            }
        } catch (\Exception $e) {
            $this->error('Failed to delete old files: ' . $e->getMessage());
        }

        $this->info('Uploading new backup to Google Drive...');

        // 5️⃣ Upload new backup
        try {
            $fileMetadata = new DriveFile([
                'name' => basename($backupPath),
                'parents' => [$folderId],
            ]);

            $drive->files->create($fileMetadata, [
                'data' => file_get_contents($backupPath),
                'uploadType' => 'multipart',
            ]);

            $this->info('Backup uploaded to Google Drive successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to upload backup: ' . $e->getMessage());
        }
    }
}
