<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class guideController extends Controller
{
    public function index()
    {
        $guides = User::withCount(['guideAssignments as assignments_count'])
            ->where('role', 'tour_guide')
            ->orderBy('name')
            ->get();

        return view('admin.mana_guide.index', compact('guides'));
    }
}
