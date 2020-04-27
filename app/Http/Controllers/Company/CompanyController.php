<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Resources\CompanyResource;

class CompanyController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function index(Request $request)
    // {
    //     return CompanyResource::collection(withRelations(Company::filter($request)->paginate(10)->appends($request->except('page'))))->response()->setStatusCode(200);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(Request $request)
    // {
    //     $company = Company::create($request->all());

    //     return response(new CompanyResource($company), 201);
    // }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Company $company)
    {
        if (!$request->user()->hasAnyRole('admin')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        return response(new CompanyResource(withRelations($company)), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        if (!$request->user()->hasAnyRole('admin')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        $company->update($request->all());

        return response(new CompanyResource($company), 200);
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Models\Company  $company
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy(Company $company)
    // {
    //     $company->delete();

    //     return response(new CompanyResource($company), 201);
    // }
}
