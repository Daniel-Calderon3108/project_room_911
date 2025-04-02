// Initiate the main panel
window.onload = function () {
    currentTime();

    let debounceTimer;
    let countInput = 0;

    // Validate input if it has been touched
    $('#form-employee-data input').on('keyup', function () {
        // It increases to 2, not to count when the user field and password are self -reflected by the browser
        if (countInput < 2) return countInput++;

        $(this).addClass('touched');

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            let formData = $('#form-employee-data').serialize();
            validateForm(formData);
        }, 500);
    })

    // When the form is submitted with filter
    $('#form-filter').on('submit', function (e) {
        e.preventDefault();
        filterEmployees();
    });

    // Delegated event for pagination buttons
    $(document).on('click', '.page-link', function () {
        let url = $(this).data('url');
        filterEmployees(url);
    })
}

// Modals to show
const modal = [
    'form-employee',
    'history-employee',
    'modal-wait-import'
];

// Animation wait import
const animation = [
    'hourglass-start',
    'hourglass-half',
    'hourglass-end',
]

let interval; // Interval for animation

let update = 0; // Variable to check if it is an update or not
let showPassword = false; // Variable to check if the password is shown or not
let typePassword = 'password'; // Variable to check the type of password
let imagePassword = 'eye-slash'; // Variable to check the image of the password
let idEmployee = null; // Variable to check the id of the employee

// Get the current time
function currentTime(fechaStr = null) {
    var date = fechaStr ? new Date(fechaStr) : new Date(); // Or the date you'd like converted.
    var day = String(date.getUTCDate()).padStart(2, '0');
    var month = String(date.getUTCMonth() + 1).padStart(2, '0');
    var year = date.getUTCFullYear();
    var hours = date.getUTCHours(); // Hours part from the timestamp
    var minutes = date.getUTCMinutes(); // Minutes part from the timestamp
    var seconds = date.getUTCSeconds(); // Seconds part from the timestamp
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12; // Hours must be between 1-12
    hours = hours ? hours : 12; // The hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes; // Minutes must be between 00-59
    seconds = seconds < 10 ? '0' + seconds : seconds; // Seconds must be between 00-59
    var strTime = day + '-' + month + '-' + year + " " + hours + ':' + minutes + ':' + seconds + ' ' + ampm; // Display the time

    if (fechaStr) return strTime;

    document.getElementById('current-time').innerHTML = strTime; // Display the time
    setTimeout(currentTime, 1000); // Update the time every second 
}

// Set Form Employee
function setFormEmployee(edit, employeeId = null) {
    $('.error_form').text(''); // Clear all errors
    $('.error_form').addClass('hidden'); // Hide all errors
    $('#form-employee-data input').removeClass("touched"); // Remove touched class from all inputs
    $('#submit').prop('disabled', true); // Disable submit button
    document.getElementById('password').value = '';

    if (edit) {

        $.ajax({
            url: `get_employee/${employeeId}`,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let role = response.data.user.role_id ? response.data.user.role_id : 0;

                    document.getElementById('form-employee-title').innerHTML = 'Edit Employee';
                    document.getElementById('employee_id').value = response.data.id;
                    document.getElementById('user_id').value = response.data.user_id;
                    document.getElementById('name').value = response.data.name;
                    document.getElementById('last_name').value = response.data.last_name;
                    document.getElementById('department_id').value = response.data.department_id;
                    document.getElementById('user').value = response.data.user.name;
                    document.getElementById('role_id').value = role;
                    document.getElementById('form-import').classList.add('hidden');
                    update = 1;
                    idEmployee = employeeId;
                    showModal('form-employee');
                } else {
                    console.error(response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr);
            }
        });
    }

    if (!edit) {
        document.getElementById('form-employee-title').innerHTML = 'Add Employee';
        document.getElementById('employee_id').value = '';
        document.getElementById('user_id').value = '';
        document.getElementById('name').value = '';
        document.getElementById('last_name').value = '';
        document.getElementById('department_id').value = '1';
        document.getElementById('user').value = '';
        document.getElementById('role_id').value = '0';
        document.getElementById('form-import').classList.remove('hidden');
        update = 0;
        showModal('form-employee');
    }
}

// Show modals
function showModal(modalId) {
    $('body').css('overflow', 'hidden'); // Disable scroll
    if (document.getElementById('modals').classList.contains('hidden')) {
        document.getElementById('modals').classList.remove('hidden');
    }

    modal.forEach(function (item) {
        if (item === modalId) {
            document.getElementById(item).classList.remove('hidden');
        } else {
            document.getElementById(item).classList.add('hidden');
        }
    });
}

// Hide modals
function hideModal() {
    $('body').css('overflow', ''); // Enable scroll
    document.getElementById('modals').classList.add('hidden');
    modal.forEach(function (item) {
        document.getElementById(item).classList.add('hidden');
    });
}

// Enable / Disable Employee
function toggleEmployeeStatus(userId) {

    $.ajax({
        url: `changeActive/${userId}`,
        dataType: 'JSON',
        method: 'PUT',
        success: function (response) {
            if (response.success) {
                let newStatus = response.data ? 'Disable' : 'Enable';
                document.getElementById('status-' + userId).innerHTML = newStatus;
            } else {
                console.error(response.message);
            }
        },
        error: function (xhr) {
            console.error(xhr);
        }
    })
}

// Get History Employee
function getHistoryEmployee(employeeId) {

    // Get history employee
    $.ajax({
        url: `history/${employeeId}`,
        method: 'GET',
        success: function (response) {
            if (response.success) {
                let table = ``;

                response.data.forEach(element => {
                    table += `
                                <tr>
                                    <td>${element.employee_id}</td>
                                    <td>${currentTime(element.created_at)}</td>
                                    <td>${element.success ? "Yes" : "No"}</td>
                                    <td>${element.reason}</td>
                                </tr>
                            `;
                });

                document.getElementById('history_table').innerHTML = table;
                showModal('history-employee');
            } else {
                console.error(response.message);
            }
        },
        error: function (xhr) {
            console.error(xhr)
        }
    });
}

// Show / Hide Password
function showHidePassword() {
    showPassword = !showPassword;
    typePassword = showPassword ? 'text' : 'password';
    imagePassword = showPassword ? 'eye' : 'eye-slash';
    document.getElementById('show-password').src = `../assets/${imagePassword}.svg`;
    document.getElementById('password').type = typePassword;
}

// Validate Form
function validateForm(formData) {
    // Validate if the password is empty
    if (document.getElementById('password').value === '' && update) {
        // If the password is empty and it is an update, it is eliminated from the formdata
        formData = formData.replace(/&password=[^&]*/g, '');
    }

    $.ajax({
        url: `validate_form/${update ? idEmployee : '-1'}`,
        method: 'POST',
        data: formData,
        success: function () {
            $('.error_form').text(''); // Clear all errors
            $('.error_form').addClass('hidden');
            $('#submit').prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $('.error_form').text(''); // Clear all errors
                $('.error_form').addClass('hidden');
                $('#submit').prop('disabled', true);
                for (let key in errors) {
                    let input = $(`#${key}`); // Get input element
                    if (input.hasClass('touched')) {
                        $(`#error-${key}`).text(errors[key]);
                        $(`#error-${key}`).removeClass('hidden');
                    }
                }
            }
        }
    });
}

// Submit Form
function submitEmployee(event) {
    event.preventDefault();
    let formData = $('#form-employee-data').serialize();
    let url = update ? 'update_employee/' + idEmployee : 'store_employee';
    let method = update ? 'PUT' : 'POST';

    if (update && document.getElementById('password').value === '') {
        formData = formData.replace(/&password=[^&]*/g, '');
    }

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function (response) {
            if (response.success) {
                hideModal();
                alertPredefined("success", "Success!", response.message).then(() => { location.reload() });
            } else {
                console.error(response.message);
            }
        },
        error: function (xhr) {
            console.error(xhr);
        }
    });
}

// Import Employees
function importEmployees(event) {
    event.preventDefault();
    showModal('modal-wait-import');
    animationWaitImport();

    let file = new FormData();
    file.append('import', document.getElementById('import').files[0]);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: 'store_employees',
        method: 'POST',
        data: file,
        contentType: false,
        processData: false,
        success: function (response) {
            hideModal();
            stopAnimation();
            if (response.success) {
                alertPredefined("success", "Success!", response.message).then(() => { location.reload() });
            } else {
                alertPredefined("error", "Error!", response.message);
            }
        },
        error: function (xhr) {
            hideModal();
            stopAnimation();
            alertPredefined("error", "Error!", xhr.responseJSON.message);
        }
    });
}

// Delete Employee
function deleteEmployee(employeeId, userId) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: `delete_employee/${employeeId}/${userId}`,
                method: 'DELETE',
                success: function (response) {
                    if (response.success) {
                        alertPredefined("success", "Deleted!", "Your file has been deleted.").then(() => { location.reload() });
                    } else {
                        console.error(response.message);
                    }
                },
                error: function (xhr) {
                    console.error(xhr);
                }
            })
        }
    });
}

// Filter Employees
function filterEmployees(url = `filter_employee`) {
    let formData = $('#form-filter').serialize();

    $.ajax({
        url: url,
        method: 'GET',
        data: formData,
        success: function (response) {
            if (response.success) {
                let table = ``;

                response.data.data.forEach(element => {
                    table += `
                                <tr>
                                    <td>${element.id}</td>
                                    <td>${element.name}</td>
                                    <td>${element.last_name}</td>
                                    <td>${element.department.name}</td>
                                    <td>${element.history_access_count}</td>
                                    <td class="btns">
                                        <button class="btn update" onclick="setFormEmployee(true, ${element.id})">Update</button>
                                        <button class="btn disable" onclick="toggleEmployeeStatus('${element.user_id}')" id="status-${element.user_id}">
                                            ${element.active ? "Disable" : "Enable"}
                                        </button>
                                        <button class="btn history" onclick="getHistoryEmployee(${element.id})">History</button>
                                        <button class="btn delete" onclick="deleteEmployee(${element.id}, ${element.user_id})">Delete</button>
                                    </td>
                                </tr>
                            `;
                });

                document.getElementById('employee_export').value = document.getElementById('employee').value;
                document.getElementById('department_export').value = document.getElementById('department').value;
                document.getElementById('initial_export').value = document.getElementById('initial').value;
                document.getElementById('final_export').value = document.getElementById('final').value;
                document.getElementById('employee_table').innerHTML = table;
                renderPagination(response.data);

            } else {
                console.error(response.message);
            }
        },
        error: function (xhr) {
            console.error(xhr.message);
        }
    })
}

// Generate Pagination
function renderPagination(data) {
    let pagination = '';

    if (data.last_page > 1) {
        // Previous button
        if (data.prev_page_url) {
            pagination += `<button class="page-link" data-url="${data.prev_page_url}">Previous</button>`;
        }

        // Number buttons
        for (let i = 1; i <= data.last_page; i++) {
            pagination += `<button class="page-link ${i === data.current_page ? 'active' : ''}" data-url="${data.path}?page=${i}">${i}</button>`;
        }

        // Next button
        if (data.next_page_url) {
            pagination += `<button class="page-link" data-url="${data.next_page_url}">Next</button>`;
        }
    }
    $('#pagination').html(pagination);
}

// Clear Filter
function clearFilter(event) {
    event.preventDefault();
    document.getElementById('form-filter').reset();
    filterEmployees();
}

// Alert Predefined
function alertPredefined(icon, title, text) {
    return Swal.fire({
        icon: icon,
        title: title,
        text: text
    });
}

// Animation wait import
function animationWaitImport() {
    let i = 0;
    interval = setInterval(() => {
        document.getElementById('wait-import').src = `../assets/${animation[i]}.svg`;
        i++;
        if (i === 3) {
            i = 0;
        }
    }, 500);
}

// Stop animation wait import
function stopAnimation() {
    clearInterval(interval);
    document.getElementById('wait-import').src = `../assets/${animation[0]}.svg`;
}