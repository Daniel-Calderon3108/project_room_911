@extends('layouts.header.header')

@section('title', 'Main Panel')
@section('title_header', 'ROOM 911')

@section('content')
    <section class="title_menu">
        <h2>Administrative Menu</h2>
    </section>
    <section class="info_user">
        <p id="current-time"></p>
        <form action="/logout" method="POST">
            @csrf
            <button class="close_session">Close session</button>
        </form>
        <p class="right">Welcome: {{ Auth::user()->employee->name . " " . Auth::user()->employee->last_name }}</p>
    </section>
    <section class="filter">
        <form id="form-filter">
            <div class="item">
                <input type="text" name="employee" id="employee" placeholder="Search by employee name or id" autocomplete="off">
            </div>
            <div class="item">
                <select name="department" id="department">
                    <option value="">Filter by department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>          
                    @endforeach
                </select>
            </div>
            <div class="item">
                <label for="initial">Initial access date</label>
                <input type="date" name="initial" id="initial">
            </div>
            <div class="item">
                <label for="final">Final access date</label>
                <input type="date" name="final" id="final">
            </div>
            <div class="item button" onclick="filterEmployees()">
                <button>Filter</button>
            </div>
            <div class="item button" onclick="clearFilter(event)">
                <button>Clear Filter</button>
            </div>
        </form>
    </section>
    <section class="new">
        <form action="{{  route('export_employees') }}" method="GET">
            @csrf
            <input type="text" name="employee" id="employee_export" value="" hidden>
            <input type="text" name="department" id="department_export" value="" hidden>
            <input type="text" name="initial" id="initial_export" value="" hidden>
            <input type="text" name="final" id="final_export" value="" hidden>
            <input type="hidden" name="export" value="1">
            <button class="export">Export PDF</button>
        </form>
        <button onclick="setFormEmployee(false)">New employee</button>
    </section>
    <section class="table">
        <table class="table_employee">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>First name</th>
                    <th>Last name</th>
                    <th>Department</th>
                    <th>Total access</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="employee_table">
                @foreach ($employees as $employee)
                    <tr>
                        <td>{{ $employee->id }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->last_name }}</td>
                        <td>{{ $employee->department->name }}</td>
                        <td>{{ $employee->count_access }}</td>
                        <td class="btns">
                            <button class="btn update" onclick="setFormEmployee(true, {{ $employee->id }})">Update</button>
                            <button class="btn disable" onclick="toggleEmployeeStatus('{{$employee->user->id}}')" id="status-{{$employee->user->id}}">
                                {{ $employee->user->active ? "Enable" : "Disable" }}
                            </button>
                            <button class="btn history" onclick="getHistoryEmployee({{ $employee->id }})">History</button>
                            <button class="btn delete" onclick="deleteEmployee({{ $employee->id }}, {{ $employee->user->id }})">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div id="pagination" class="pagination">{{ $employees->links() }}</div>
    </section>

    {{-- Modals --}}
    <section id="modals" class="modals hidden">
        
        {{-- Modal Form Employee --}}
        <article class="form_employee hidden" id="form-employee">
            <h2 id="form-employee-title"></h2>
            <button class="close" onclick="hideModal()">X</button>
            <form class="import" id="form-import" enctype="multipart/form-data">
                <label for="import">Import CSV</label>
                <input type="file" name="import" id="import" onchange="importEmployees(event)" accept=".csv">
            </form>
            <form class="form" id="form-employee-data" onsubmit="submitEmployee(event)">
                @csrf
                <input type="hidden" name="user_id" id="user_id">
                <input type="hidden" name="employee_id" id="employee_id">
                <div class="item">
                    <input type="text" name="name" id="name" placeholder="First name" autocomplete="off">
                    <span class="error_form hidden" id="error-name"></span>
                </div>
                <div class="item">
                    <input type="text" name="last_name" id="last_name" placeholder="Last name" autocomplete="off">
                    <span class="error_form hidden" id="error-last_name"></span>
                </div>
                <div class="item">
                    <select name="department_id" id="department_id">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="item">
                    <input type="text" name="user" id="user" placeholder="User" autocomplete="off">
                    <span class="error_form hidden" id="error-user"></span>
                </div>
                <div class="item">
                    <div class="item_pass">
                        <input type="password" name="password" id="password" placeholder="Password">
                        <figure onclick="showHidePassword()">
                            <img src="{{ asset('assets/eye-slash.svg') }}" alt="Show password" id="show-password">
                        </figure>
                    </div>
                    <span class="error_form hidden" id="error-password"></span>
                </div>
                <div class="item hidden" id="active_form">
                    <select name="active" id="active">
                        <option value="1">Enable</option>
                        <option value="0">Disable</option>
                    </select>
                </div>
                <button id="submit" disabled>Save</button>
            </form>
        </article>

        {{-- Modal History Employee --}}
        <article class="history_employee hidden" id="history-employee">
            <h2>History Employee</h2>
            <button class="close" onclick="hideModal()">X</button>
            <main>
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Access date time</th>
                            <th>Success</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody id="history_table"></tbody>
                </table>
            </main>
        </article>

        {{-- Modal Charge Import CSV --}}
        <article class="modal-wait-import hidden" id="modal-wait-import">
            <img src="{{ asset('assets/hourglass-start.svg') }}" id="wait-import">
            <main>
                <p>Importing employees, please wait...</p>
            </main>
        </article>

    </section>

    <script src="{{ asset('js/main-panel.js') }}"></script>
@endsection