@extends('layouts.AdminMaster')
@section('content')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-9">
            <h2>News Management</h2>
            <ol class="breadcrumb" style="font-size:17px;color:#000">
                <li>
                    <a href="{{ route('company.dashboard') }}">Dashboard</a>
                </li>
                <span style="font-size:25px" class="fa fa-angle-double-right"></span>
                <li class="breadcrumb-item active">
                    <strong>News</strong>
                </li>
            </ol>
        </div>

        <div class="col-lg-2">
            <h2>Current Date</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <?php use Carbon\Carbon;
                        $carbon = Carbon::now();
                        $carbon1 = Carbon::now()->toDateString();
                        echo $carbon->format('l');
                        echo ' , ';
                        echo $carbon1;
                        ?>
                    </strong>
                </li>
            </ol>
        </div>

        <div class="col-lg-1">
            <h2>Time</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <strong>
                        <table>
                            <tr>
                                <td id="Hour" style="color:green;font-size:large;"></td>
                                <td id="Minut" style="color:green;font-size:large;"></td>
                                <td id="Second" style="color:red;font-size:large;"></td>
                            </tr>
                        </table>
                    </strong>
                </li>
            </ol>
        </div>
    </div>

    <script>
        function timedMsg() {
            setInterval(change_time, 1000);
        }

        function change_time() {
            var d = new Date();
            document.getElementById('Hour').innerHTML = d.getHours() + ':';
            document.getElementById('Minut').innerHTML = d.getMinutes() + ':';
            document.getElementById('Second').innerHTML = d.getSeconds();
        }
        timedMsg();
    </script>

    <div class="col-12">
        <h3 class="mb-2 page-title">News Details</h3>

        @can('Register-News')
            <button style="position:absolute; top:4.5%; right:1.7%;" class="btn mb-2 btn-primary" data-toggle="modal"
                data-target="#newsCreateModal">
                Add News
            </button>
        @endcan
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title bg-info">
                        <h5>News Table</h5>
                    </div>

                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Image</th>
                                        <th>Publish</th>
                                        <th>Expire</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($news as $k => $n)
                                        @php
                                            $publishAt = $n->publish_at ? \Carbon\Carbon::parse($n->publish_at) : null;
                                            $expiresAt = $n->expires_at ? \Carbon\Carbon::parse($n->expires_at) : null;
                                            $isExpired = $expiresAt ? $expiresAt->isPast() : false;

                                            $badgeClass =
                                                $n->status == 'published'
                                                    ? 'success'
                                                    : ($n->status == 'draft'
                                                        ? 'warning'
                                                        : 'secondary');
                                            if ($isExpired && $n->status == 'published') {
                                                $badgeClass = 'danger';
                                            }
                                        @endphp

                                        <tr>
                                            <td>{{ $k + 1 }}</td>
                                            <td>{{ $n->title }}</td>
                                            <td>{{ $n->content }}</td>
                                            <td>
                                                @if ($n->image)
                                                    <a href="{{ asset($n->image) }}" target="_blank"
                                                        rel="noopener noreferrer">
                                                        <img src="{{ asset($n->image) }}"
                                                            style="width:60px;height:40px;object-fit:cover;border-radius:6px;">
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $publishAt ? $publishAt->format('d-M-Y H:i') : '-' }}</td>
                                            <td>{{ $expiresAt ? $expiresAt->format('d-M-Y H:i') : '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $badgeClass }}">
                                                    {{ $isExpired && $n->status == 'published' ? 'EXPIRED' : strtoupper($n->status) }}
                                                </span>
                                            </td>

                                            <td>
                                                @can('Edit-News')
                                                    <button class="btn btn-sm btn-warning btn-edit-news"
                                                        data-id="{{ encrypt($n->id) }}" data-title="{{ e($n->title) }}"
                                                        data-content="{{ e($n->content) }}"
                                                        data-publish_at="{{ $publishAt ? $publishAt->format('Y-m-d\TH:i') : '' }}"
                                                        data-expires_at="{{ $expiresAt ? $expiresAt->format('Y-m-d\TH:i') : '' }}"
                                                        data-status="{{ $n->status }}"
                                                        data-image="{{ $n->image ? asset($n->image) : '' }}">
                                                        Edit
                                                    </button>
                                                @endcan

                                                @can('Delete-News')
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-danger btn-delete-news"
                                                        data-id="{{ encrypt($n->id) }}">
                                                        Remove
                                                    </a>
                                                @endcan
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No news found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div class="modal fade" id="newsCreateModal">
        <div class="modal-dialog modal-lg">
            <form id="newsCreateForm" action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">

                    <div class="modal-header">
                        <h5>Add News</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Content *</label>
                            <textarea name="content" class="form-control" rows="5" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label>Publish</label>
                                <input type="datetime-local" name="publish_at" class="form-control">
                            </div>
                            <div class="col">
                                <label>Expire</label>
                                <input type="datetime-local" name="expires_at" class="form-control">
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label>Image (Image Only)</label>
                            <input type="file" name="image" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="draft">Draft</option>
                                <option value="published" selected>Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button onclick="handleConfirmSubmit('newsCreateForm')" class="btn btn-primary">Submit</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="newsEditModal">
        <div class="modal-dialog modal-lg">
            <form id="newsEditForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-content">

                    <div class="modal-header">
                        <h5>Edit News</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="edit_news_id">

                        <div class="form-group">
                            <label>Title *</label>
                            <input id="edit_title" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Content *</label>
                            <textarea id="edit_content" name="content" class="form-control" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label>Publish</label>
                                <input id="edit_publish_at" type="datetime-local" name="publish_at"
                                    class="form-control">
                            </div>
                            <div class="col">
                                <label>Expire</label>
                                <input id="edit_expires_at" type="datetime-local" name="expires_at"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label>Image (Image Only)</label>
                            <input type="file" name="image" class="form-control">
                            <img id="imagePreview" style="max-width:100px;margin-top:5px;display:none;">
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select id="edit_status" name="status" class="form-control">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button onclick="handleConfirmSubmit('newsEditForm')" class="btn btn-primary">Update</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-edit-news').forEach(btn => {
            btn.addEventListener('click', function() {

                let id = this.dataset.id;

                document.getElementById('edit_title').value = this.dataset.title;
                document.getElementById('edit_content').value = this.dataset.content;
                document.getElementById('edit_publish_at').value = this.dataset.publish_at;
                document.getElementById('edit_expires_at').value = this.dataset.expires_at;
                document.getElementById('edit_status').value = this.dataset.status;

                let img = this.dataset.image;
                if (img) {
                    let preview = document.getElementById('imagePreview');
                    preview.src = img;
                    preview.style.display = 'block';
                }

                document.getElementById('newsEditForm').action = "{{ route('news.update', ':id') }}"
                    .replace(':id', id);

                $('#newsEditModal').modal('show');
            });
        });

        document.querySelectorAll('.btn-delete-news').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;

                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true
                }).then(res => {
                    if (res.isConfirmed) {
                        window.location.href = "{{ route('news.remove', ':id') }}".replace(':id',
                            id);
                    }
                });
            });
        });
    </script>
@endsection
