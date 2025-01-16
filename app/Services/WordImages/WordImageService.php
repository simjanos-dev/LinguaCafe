<?php

namespace App\Services\WordImages;

use App\Models\User;
use App\Models\Phrase;
use App\Models\EncounteredWord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WordImageService 
{
    public function __construct() 
    {
        //
    }

    public function setImageFromUrl(EncounteredWord|Phrase $model, User $user, string $url): string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $fileName = $model->id . '.' . $extension;
        $type = ($model instanceof EncounteredWord) ? 'words' : 'phrases';


        $context  = stream_context_create(
        [
            'http' => [
            'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
            ],
        ]);
        $image = file_get_contents($url, false, $context);
        Storage::put('/images/word_images/' . $type . '/' . $user->id . '/' . $fileName, $image);

        $model->image = $fileName;
        $model->save();

        return $fileName;
    }
    
    public function uploadImage(User $user, EncounteredWord|Phrase $model, UploadedFile $imageFile): string
    {
        if ($model->user_id !== $user->id) {
            throw new \Exception('Image does not belong to this user.', 401);
        }

        $extension = $imageFile->clientExtension();
        $fileName = $model->id . '.' .  $extension;
        $type = ($model instanceof EncounteredWord) ? 'words' : 'phrases';

        $imageFile->move(storage_path('app/images/word_images/' . $type . '/' . $user->id), $fileName);

        $model->image = $fileName;
        $model->save();

        return $fileName;
    }

    public function getImagePath(User $user, EncounteredWord|Phrase $model): string
    {
        if ($model->user_id !== $user->id) {
            throw new \Exception('Image does not belong to this user.', 401);
        }

        if ($model->image === null) {
            throw new \Exception('This word or phrase does not have an image.', 404);
        }

        $type = ($model instanceof EncounteredWord) ? 'words' : 'phrases';
        
        return Storage::path('/images/word_images/' . $type . '/' . $user->id . '/' . $model->image);
    }

    public function deleteImage(User $user, EncounteredWord|Phrase $model): void
    {
        if ($model->user_id !== $user->id) {
            throw new \Exception('Image does not belong to this user.', 401);
        }

        if ($model->image === null) {
            throw new \Exception('This word or phrase does not have an image.', 500);
        }

        $type = ($model instanceof EncounteredWord) ? 'words' : 'phrases';
        
        Storage::delete('/images/word_images/' . $type . '/' . $user->id . '/' . $model->image);

        $model->image = null;
        $model->save();
    }
}