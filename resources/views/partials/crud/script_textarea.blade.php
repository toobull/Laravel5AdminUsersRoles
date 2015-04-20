@if (\App\Profile::loginProfile()->ckeditor=="TinyMCE")
    <script src="{{asset('/js/editor/tinymce/tinymce.min.js')}}"></script>
    <script type="text/javascript">
        tinymce.init({
            selector: "textarea",
            language: '{{App::getLocale()}}',
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table contextmenu paste"
            ],
            toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
        });
    </script>
@else
    <script src="{{asset('/js/editor/ckeditor/'.(\App\Profile::loginProfile()->ckeditor?:'basic').'/ckeditor.js')}}"></script>
    <script type="text/javascript">
        @foreach ($fields as $field)
        CKEDITOR.replace('{{$field}}',{
            language: '{{App::getLocale()}}',
            autoGrow_maxHeight: 600
        });
        @endforeach
    </script>
@endif