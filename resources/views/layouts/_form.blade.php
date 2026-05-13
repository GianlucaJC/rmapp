@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="title" class="form-label">Titolo</label>
    <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $news->title) }}" required>
</div>

<div class="mb-3">
    <label for="content" class="form-label">Contenuto</label>
    <textarea class="form-control" id="content" name="content" rows="10">{{ old('content', $news->content) }}</textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_date" class="form-label">Data Inizio Pubblicazione</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $news->start_date ? $news->start_date->format('Y-m-d') : '') }}">
        <small class="form-text text-muted">Lasciare vuoto per non impostare una data di inizio.</small>
    </div>
    <div class="col-md-6 mb-3">
        <label for="end_date" class="form-label">Data Fine Pubblicazione</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $news->end_date ? $news->end_date->format('Y-m-d') : '') }}">
        <small class="form-text text-muted">Lasciare vuoto per non impostare una data di fine.</small>
    </div>
</div>

<div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="is_suspended" name="is_suspended" value="1" @if(old('is_suspended', $news->is_suspended)) checked @endif>
    <label class="form-check-label" for="is_suspended">Sospendi manualmente la news</label>
</div>

<div class="d-flex justify-content-end">
    <a href="{{ route('admin.news.index') }}" class="btn btn-secondary me-2">Annulla</a>
    <button type="submit" class="btn btn-primary">Salva</button>
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/e7nmdbjjd7ottq9gh2b6eiafpymt5tywdawssgbc6rm3071b/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: 'textarea#content',
        plugins: 'code table lists link image',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table | link image',
        height: 400,
    });
</script>
@endpush