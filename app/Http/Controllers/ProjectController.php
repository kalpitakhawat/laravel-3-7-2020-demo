<?php

namespace App\Http\Controllers;

use App\Bid;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Auth;

class ProjectController extends Controller
{
    /**
     * create
     *
     * @param  mixed $request
     * @return mixed $response
     */
    public function create(Request $request)
    {
        // Request validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'tags' => 'required|string',
            'files' => 'required|array',
            'files.*' => 'required|File',
        ]);
        if ($validator->fails()) {
            return Response::json([
                "message" => "Invalid Parameters",
                "errors" => $validator->messages()
            ], 422);
        };

        // Project Object formation
        $projectObject = [
            "title" => $request->title,
            "description" => $request->description,
            "tags" => $request->tags,
            "user_id" => Auth::id(),
            "files" => ''
        ];

        // Files Save to folder
        $files = $request->file('files');
        $filesPathArray = [];
        foreach ($files as $k => $file) {
            $extension = $file->getClientOriginalExtension();
            $filename = time() . $k . '.' . $extension;
            $file->move(public_path() . '/uploads/', $filename);
            array_push($filesPathArray, '/uploads/' . $filename);
        }
        $projectObject["files"] = implode(',', $filesPathArray);

        // Object save to db
        $project = new Project($projectObject);
        $project->save();

        // Response
        return Response::json([
            'message' => 'Project Created Successfully!',
            "project" => $project
        ], 200);
    }

    /**
     * bid
     *
     * @param  mixed $request
     * @return mixed $response
     */
    public function bid(Request $request)
    {
        // Request Validation
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|numeric',
            'amount' => 'required|numeric',
            'hours' => 'required|numeric',
            'notes' => 'required|string',
        ]);
        if ($validator->fails()) {
            return Response::json([
                "message" => "Invalid Parameters",
                "errors" => $validator->messages()
            ], 422);
        };

        $request->request->add(['user_id' => Auth::id()]);

        // Check for Bid Already Placed
        $placedBid = Bid::where('project_id', $request->project_id)->where('user_id', Auth::id())->first();
        if (!empty($placedBid)) {
            return Response::json([
                "message" => "Already Bid Placed on this project!",
            ], 400);
        }
        // Save to db
        $bid = new Bid($request->all());
        $bid->save();

        // Resposne
        return Response::json([
            'message' => 'Bid Placed Successfully!',
            "bid" => $bid
        ], 200);
    }


    /**
     * index
     *
     * @param  mixed $request
     * @return mixed $resposne
     */
    public function index(Request $request)
    {
        $projects = Project::query();

        // Title Filter
        if (!empty($request->title)) {
            $projects = $projects->where('title', 'LIKE', '%' . $request->title . '%');
        }

        // Has File Filter
        if (!empty($request->hasFile)) {
            if ($request->hasFile == 'true') {
                $projects = $projects->where('files', '!=', '');
            } else {
                $projects = $projects->where(function ($query) {
                    return $query->where('files', '=', '')->orWhereNull('files');
                });
            }
        }

        // Tags Filter
        if (!empty($request->tags)) {
            $tags = explode(',', $request->tags);
            $projects = $projects->where(function ($query) use ($tags) {
                foreach ($tags as $key => $tag) {
                    if ($key = 0) {
                        $query = $query->where('tags', 'LIKE', '%' . $tag . '%');
                    } else {
                        $query = $query->orWhere('tags', 'LIKE', '%' . $tag . '%');
                    }
                }
                return $query;
            });
        }

        // Attach user details
        $projects = $projects->with(['client']);

        // Resposne
        return response()->json([
            "message" => "Projects Retrived",
            "Projects" => $projects->paginate(10)
        ], 200);
    }
}
