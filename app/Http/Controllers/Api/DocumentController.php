<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator,Redirect,Response,File;
use App\Models\Documents;
use App\Http\Controllers\Api;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
 
       $validator = Validator::make($request->all(), 
              [ 
              'user_id' => 'required',
              'file' => 'required|mimes:doc,docx,pdf,txt|max:2048',
             ]);   
 
    if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
         }  
 
  
        if ($files = $request->file('file')) {
             
            //store file into document folder
            $file = $request->file->store('public/documents');
 
            //store your file into database
            $document = new Documents();
            $document->title = $file;
            $document->user_id = $request->user_id;
            $document->save();
			$doc_id = $document->id;
              
            return response()->json([
                "success" => true,
                "message" => "File successfully uploaded",
                "file" => $file,
				"document_id" => $doc_id
            ]);
			
			
  
        }
 
  
    }
	
	
	public function esign(Request $request)
    {
 
       $validator = Validator::make($request->all(), 
              [ 
              'doc_id' => 'required',
             ]);   
 
    if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
         }  
 
  
        if ($doc_id = $request->doc_id) {
			
			
			
			
			$data = [
					  'document_id' => '{document_id}',
					   'subject' => 'My email subject',
					   'message' => 'My email message',    
					   'signers' => [
						'email' => 'invited_signer@email.com',
						 'fields' => [
						  'page' => 0,
						   'rectangle' => [0, 0, 200, 100],
						   'type' => 'SIGNATURE'    
						  ] 
						] 
					  ];

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://api.digisigner.com/v1/signature_requests",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30000,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => json_encode($data),
				CURLOPT_HTTPHEADER => array(
					// Set here requred headers
					"content-type: application/json",
				),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				echo "cURL Error #:" . $err;
			} else {
				return response()->json([
                "success" => true,
                "response" => $response
            ]);
			}
			
			
  
        }
 
  
    }
}
