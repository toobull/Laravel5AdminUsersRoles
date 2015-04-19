<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

use App\Models\Admin\Fileentry;
use App\Profile;
use DB;
use Exception;
use Excel;

use Config;
use Storage;
use File;

use App\Http\Requests\Admin\FileentryRequest as ModelRequest;
use App\Http\Requests\Admin\DeleteRequest as DeleteRequest;
use App\Http\Requests\Admin\FileentrySearchRequest as SearchRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class FileentriesController extends Controller
{


    protected $model_name = 'Fileentry';
    protected $index_view = 'admin.fileentries.index';
    protected $create_view = 'admin.fileentries.create';
    protected $show_view = 'admin.fileentries.show';
    protected $edit_view = 'admin.fileentries.edit';

    protected $index_route = 'admin.fileentries.index';
    protected $create_route = 'admin.fileentries.create';
    protected $show_route = 'admin.fileentries.show';
    protected $edit_route = 'admin.fileentries.edit';
    protected $trash_route = 'admin.fileentries.trash';


    protected $sort_fields = [
        'id',
        'original_name',
        'original_mime_type',
        'original_extension',
        'name',
        'mime_type',
        'extension',
        'size',
    ];
    protected $filter_fields = [
        'id',
        'original_name',
        'original_mime_type',
        'original_extension',
        'name',
        'mime_type',
        'extension',
        'size',
    ];

    public function __construct()
    {
        $this->middleware('admin');
    }


    public function show_trash()
    {
        return Session($this->index_view . '.trash', false);
    }

    public function trash($value = false)
    {
        if (isset($value)) {
            if ($value) {
                $value = true;
            } else {
                $value = false;
            }
        } else {
            $value = false;
        }
        Session([$this->index_view . '.trash' => $value]);
        return redirect(route($this->index_route));
    }

    public function filter(SearchRequest $request)
    {
        foreach ($this->filter_fields as $field) {
            Profile::loginProfile()->setFilterValue($this->index_view, $field, $request->input($field, ''));
        }
        return redirect(route($this->index_route));
    }

    public function sort($column = null, $order = null)
    {
        if (isset($order)) {
            if ($order !== 'desc') {
                $order = 'asc';
            }
        } else {
            $order = 'asc';
        };

        if (isset($column)) {
            if (in_array($column, $this->sort_fields)) {
                Profile::loginProfile()->setOrderByValue($this->index_view, $column, $order);
            }
        } else {
            Profile::loginProfile()->setOrderBy($this->index_view, []);
        };

        return redirect(route($this->index_route));
    }


    public function excel($format = 'xlsx')
    {
        $filter = $this->getFilter();
        $models = $this->getModels($filter);
        Excel::create(str_plural($this->model_name), function ($excel) use ($models) {

            // Our first sheet
            $excel->sheet(str_plural($this->model_name),
                function ($sheet) use ($models) {
                    $sheet->fromModel($models->get())
                        ->setAutoFilter();
                });
        })->export($format);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $filter = $this->getFilter();
        $models = $this->getModels($filter);
        $models = $models->paginate(Profile::loginProfile()->per_page);
        return view($this->index_view, compact('models', 'filter'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $model = new Fileentry();
        return view($this->create_view, compact(['model']));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        try {
            $model = $this->getModel($id);
            return view($this->show_view, compact('model'));
        } catch (Exception $e) {
            flash()->warning("$this->model_name $id not found");
            return $this->index();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $model = $this->getModel($id);
        return view($this->edit_view, compact(['model']));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function download($id)
    {
        try {
            $model = Fileentry::findOrFail($id);
            $file = storage_path('app/').$model->name;
            $name = pathinfo($model->original_name,PATHINFO_FILENAME) .'.' .$model->extension;
            return response()->download($file, $name,['Content-Type' => $model->mime_type]);
        } catch (Exception $e)
        {
            return (new Response($e->getMessage(), Response::HTTP_NOT_FOUND));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function get($id)
    {
        try {
            $model = Fileentry::findOrFail($id);
            $file = Storage::disk('local')->get($model->name);
            return (new Response($file, Response::HTTP_OK))->header('Content-Type', $model->mime_type);
        } catch (Exception $e)
        {
            return (new Response($e->getMessage(), Response::HTTP_NOT_FOUND));
        }
    }



    public function save_upload(UploadedFile $file)
    {
        /*
         *  pathinfo ( string $path [, int $options =
         * PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME ] )
         */

        $uploads_dir = Config::get('dirs.uploads');
        $filename =pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);
        $extension = $file->guessExtension();
        $target_name = $uploads_dir . strftime('%Y/%j/') . $filename . '.' .$extension;
        $n=1;
        while (Storage::disk('local')->exists($target_name))
        {
            $target_name = $uploads_dir . strftime('%Y/%j/') . $filename.'('.$n.').'.$extension;
            $n++;
        }
        Storage::disk('local')->put($target_name , File::get($file));
        return $target_name;

    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(ModelRequest $request)
    {
        try {
            $model = new Fileentry($request->all());
            if ($request->hasFile('file_upload')) {
                $file = $request->file('file_upload');
                if ($file->isValid()) {
                    $model->original_name = $file->getClientOriginalName();
                    $model->original_mime_type = $file->getClientMimeType();
                    $model->original_extension = $file->getClientOriginalExtension();
                    $model->name = $this->save_upload($file);;
                    $model->mime_type = $file->getMimeType();
                    $model->extension = $file->guessExtension();
                    $model->size = $file->getSize();
                }
            }
            try {
                DB::beginTransaction();
                $model->save();
                DB::commit();
                flash()->info("$this->model_name saved");
                return redirect(route($this->show_route, [$model->id]));
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $errors = [];
            flash()->error($e->getMessage());
            return $request->response($errors);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id, ModelRequest $request)
    {
        try {
            $model = $this->getModel($id);
            try {
                DB::beginTransaction();
                $model->update($request->all());
                DB::commit();
                flash()->info("$this->model_name saved");
                return redirect(route($this->show_route, [$model->id]));
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $errors = [];
            flash()->error($e->getMessage());
            return $request->response($errors);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $model = $this->getModel($id);
            $model->delete();
            flash()->info("$this->model_name sent to trash");
            if ($this->show_trash()) {
                return redirect(route($this->show_route, [$id]));
            } else {
                return redirect(route($this->index_route));
            }
        } catch (Exception $e) {
            flash()->error($e->getMessage());
            return $request->response([]);
        }
    }


    public function restore($id, DeleteRequest $request)
    {
        try {
            $model = $this->getModel($id);
            $model->restore();
            flash()->info("$this->model_name restored");
            return redirect(route($this->show_route, [$id]));
        } catch (Exception $e) {
            flash()->error($e->getMessage());
            return $request->response([]);
        }
    }

    public function forcedelete($id, DeleteRequest $request)
    {
        try {
            $model = $this->getModel($id);
            DB::transaction(
                function () use ($model) {
                    $model->forcedelete();
                });
            flash()->info("$this->model_name removed");
            return redirect(route($this->index_route));
        } catch (Exception $e) {
            flash()->error($e->getMessage());
            return $request->response([]);
        }
    }


    public function getModels($filter = null)
    {
        $models = Fileentry::sortable($this->index_view);
        if ($this->show_trash()) {
            $models = $models->withTrashed();
        }
        if (isset($filter)) {
            foreach ($this->filter_fields as $field) {
                if (trim($filter[$field]) != '') {
                    $values = explode(',', $filter[$field]);
                    $first = true;
                    foreach ($values as $value) {
                        if ($field == 'id') {
                            if ($first) {
                                $models = $models->Where($field, $value);
                                $first = false;
                            } else {
                                $models = $models->orWhere($field, $value);
                            }
                        } else {
                            $value = '%' . $value . '%';
                            if ($first) {
                                $models = $models->Where($field, 'LIKE', $value);
                                $first = false;
                            } else {
                                $models = $models->orWhere($field, 'LIKE', $value);
                            }
                        }
                    }
                }
            }
        }
        return $models;
    }

    public function getModel($id)
    {
        return $this->getModels()->findOrFail($id);
    }

    public function getFilter()
    {
        $values = [];
        foreach ($this->filter_fields as $field) {
            $values[$field] = Profile::loginProfile()->getFilterValue($this->index_view, $field);
        }
        return $values;
    }

}
