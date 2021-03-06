<?php namespace Modules\ModuleName\Controllers;
/*
=================================================
Module Name     :   NameOfTheModule
Module Version  :   vVersionOfTheModule
Compatible CMS  :   v1.2
Site            :   WebsiteOfTheModule
Description     :   DescriptionOfTheModule
===================================================
*/
use Backend\AdminController as BaseController;
use Redirect;
use Input;
use Str;
use View;
use Modules\ModuleName\Models\ModuleModel;

class BackendController extends BaseController {

    /**
     * The layout that should be used for responses.
     */
    protected $layout;
    // Type of the parent page
    protected $type;
    protected $config;
    protected $fields;
    protected $module_alias;
    protected $module_link;

    public function __construct()
    {
        $this->config = json_decode(file_get_contents(__DIR__ . '/../module.json'), true);
        $this->fields = $this->config['fields'];
        $this->field_names = $this->config['field_names'];
        $this->module_alias = $this->config['info']['canonical'];
        $this->module_link = Str::snake($this->module_alias, '_');

        parent::__construct();

        $this->type = $this->link_type;

        // Add location hinting for views
        View::addNamespace($this->module_alias,
            app_path() . "/modules/{$this->module_alias}/views");
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $entries = ModuleModel::all();

        $this->layout->title = "All Entries in {$this->config['info']['name']}";
        $this->layout->content = View::make("{$this->module_alias}::{$this->type}.index")
                                        ->with('title', "All Entries in {$this->config['info']['name']}")
                                        ->with('entries', $entries)
                                        ->with('fields', $this->fields)
                                        ->with('field_names', $this->field_names)
                                        ->with('module_link', $this->module_link);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->layout->title = "Add New Entry in {$this->config['info']['name']}";;
        $this->layout->content = View::make("{$this->module_alias}::{$this->type}.create_edit")
                                        ->with('title', "Add New Entry in {$this->config['info']['name']}")
                                        ->with('module_link', $this->module_link);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();

        if (isset($input['form_close'])) {
            return Redirect::to("{$this->link}modules/{$this->module_link}");
        }

        ModuleModel::create($input);

        if (isset($input['form_save'])) {
            $redirect = "{$this->link}modules/{$this->module_link}";
        } else {
            $redirect = "{$this->link}modules/{$this->module_link}/create";
        }

        // return Redirect::***REDIRECT_TO***
        return Redirect::to($redirect)
                            ->with('success_message', 'The entry has been successfully added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $entry = ModuleModel::findOrFail($id);

        $this->layout->title = "Showing Entry in {$this->config['info']['name']}";;
        $this->layout->content = View::make("{$this->module_alias}::{$this->type}.show")
                                        ->with('title', "Showing Entry in {$this->config['info']['name']}")
                                        ->with('module_link', $this->module_link)
                                        ->with('entry', $entry)
                                        ->with('field_names', $this->field_names)
                                        ->with('fields', $this->fields);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $entry = ModuleModel::findOrFail($id);
        $this->layout->title = "Edit Entry in {$this->config['info']['name']}";;

        $this->layout->content = View::make("{$this->module_alias}::{$this->type}.create_edit")
                                        ->with('title', "Edit Entry in {$this->config['info']['name']}")
                                        ->with('module_link', $this->module_link)
                                        ->with('entry', $entry)
                                        ->with('fields', $this->fields);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $input = Input::all();

        if (isset($input['form_close'])) {
            return Redirect::to("{$this->link}modules/{$this->module_link}");
        }

        if (isset($input['form_save'])) {
            $redirect = "{$this->link}modules/{$this->module_link}";
        } else {
            $redirect = "{$this->link}modules/{$this->module_link}/create";
        }

        $entry = ModuleModel::findOrFail($id);
        $entry->update($input);

        // return Redirect::***REDIRECT_TO***
        return Redirect::to($redirect)
                        ->with('success_message', 'The entry has been successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id=null)
    {
        // If multiple ids are specified
        if ($id == 'multiple') {
            $selected_ids = trim(Input::get('selected_ids'));
            if ($selected_ids == '') {
                return Redirect::back()
                                ->with('error_message', "Nothing was selected to delete");
            }
            $selected_ids = explode(' ', $selected_ids);
        } else {
            $selected_ids = array($id);
        }

        foreach ($selected_ids as $id) {
            $entry = ModuleModel::findOrFail($id);

            $entry->delete();
        }

        if (count($selected_ids) > 1) {
            $message = 'The entries were deleted';
        } else {
            $message = 'The entry was deleted';
        }

        return Redirect::back()
                            ->with('success_message', $message);
    }
}
