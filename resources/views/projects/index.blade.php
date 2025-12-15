@extends('layouts.sideBar')

@section('title', 'Projects')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Projects</h3>
                        <a href="{{ route('projects.create') }}" class="btn btn-primary float-right">Add Project</a>
                    </div>
                    <div class="card-header">
                        <form method="GET" action="{{ route('projects.index') }}" class="form-inline">
                            <div class="form-group mr-2">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search by name or secret key" value="{{ $filters['search'] ?? '' }}">
                            </div>
                            <button type="submit" class="btn btn-secondary">Filter</button>
                            <a href="{{ route('projects.index') }}"
                                class="btn btn-outline-secondary ml-2">Clear</a>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projects as $project)
                                    <tr>
                                        <td>{{ $project->id }}</td>
                                        <td>{{ $project->name }}</td>
                                        <td>{{ $project->createdBy?->name ?? $project->created_by }}</td>
                                        <td>{{ $project->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center pt-2">
            @if ($projects->hasPages())
                <nav>
                    <ul class="pagination">
                        {{-- Previous Page Link --}}
                        @if ($projects->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $projects->previousPageUrl() }}"
                                    rel="prev">&laquo;</a>
                            </li>
                        @endif

                        @php
                            $current = $projects->currentPage();
                            $last = $projects->lastPage();
                            $start = max($current - 2, 2);
                            $end = min($current + 2, $last - 1);
                        @endphp

                        {{-- First page --}}
                        <li class="page-item {{ $current === 1 ? 'active' : '' }}">
                            <a class="page-link" href="{{ $projects->url(1) }}">1</a>
                        </li>

                        {{-- Dots before start --}}
                        @if ($start > 2)
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        @endif

                        {{-- Page range --}}
                        @for ($page = $start; $page <= $end; $page++)
                            <li class="page-item {{ $current === $page ? 'active' : '' }}">
                                <a class="page-link" href="{{ $projects->url($page) }}">{{ $page }}</a>
                            </li>
                        @endfor

                        {{-- Dots after end --}}
                        @if ($end < $last - 1)
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        @endif

                        {{-- Last page --}}
                        @if ($last > 1)
                            <li class="page-item {{ $current === $last ? 'active' : '' }}">
                                <a class="page-link" href="{{ $projects->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($projects->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $projects->nextPageUrl() }}"
                                    rel="next">&raquo;</a>
                            </li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>

    </div>

    @if (session('secret_key'))
        <div class="modal fade" id="secretKeyModal" tabindex="-1" role="dialog" aria-labelledby="secretKeyModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="secretKeyModalLabel">Project Created Successfully</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Secret Key:</strong></p>
                        <div class="input-group">
                            <input type="text" class="form-control" id="secretKeyInput"
                                value="{{ session('secret_key') }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="copySecretKey">Copy</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('secret_key'))
                var secretKeyModalEl = document.getElementById('secretKeyModal');
                if (secretKeyModalEl) {
                    var secretKeyModal = new bootstrap.Modal(secretKeyModalEl);
                    secretKeyModal.show();
                }
            @endif

            var copyButton = document.getElementById('copySecretKey');
            if (copyButton) {
                copyButton.addEventListener('click', function() {
                    var secretKeyInput = document.getElementById('secretKeyInput');
                    var text = (secretKeyInput && secretKeyInput.value) ? secretKeyInput.value : '';

                    function showCopied() {
                        copyButton.textContent = 'Copied!';
                        setTimeout(function() {
                            copyButton.textContent = 'Copy';
                        }, 2000);
                    }

                    // Try modern Clipboard API first
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(function() {
                            showCopied();
                        }).catch(function() {
                            // Fallback to legacy method
                            fallbackCopy(text, secretKeyInput) && showCopied();
                        });
                    } else {
                        // Fallback for older browsers / insecure contexts
                        fallbackCopy(text, secretKeyInput) && showCopied();
                    }
                });
            }

            function fallbackCopy(text, inputEl) {
                try {
                    if (inputEl) {
                        // Select input content and copy
                        inputEl.removeAttribute('readonly');
                        inputEl.select();
                        inputEl.setSelectionRange(0, 99999);
                        var successful = document.execCommand('copy');
                        inputEl.blur();
                        inputEl.setAttribute('readonly', true);
                        return successful;
                    } else {
                        // As a last resort, create a temporary textarea
                        var textarea = document.createElement('textarea');
                        textarea.value = text;
                        textarea.style.position = 'fixed';
                        textarea.style.left = '-9999px';
                        document.body.appendChild(textarea);
                        textarea.select();
                        var successful = document.execCommand('copy');
                        document.body.removeChild(textarea);
                        return successful;
                    }
                } catch (e) {
                    console.error('Fallback: Oops, unable to copy', e);
                    return false;
                }
            }
        });
    </script>
@endpush
