<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ContactMessageController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        try {
            $data = $validator->validated();
            $data['status'] = 'unread';

            $contactMessage = ContactMessage::create($data);

            return $this->sendResponse(
                $contactMessage,
                'Contact message submitted successfully.'
            );
        } catch (Exception $error) {
            return $this->sendError(
                'Something went wrong while submitting the message.',
                ['error' => $error->getMessage()],
                500
            );
        }
    }
}
