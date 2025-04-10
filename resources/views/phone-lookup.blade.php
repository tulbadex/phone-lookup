<!DOCTYPE html>
<html>
<head>
    <title>Phone Lookup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" rel="stylesheet">
    <style>
        .bulk-results {
            max-height: 500px;
            overflow-y: auto;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .error-row {
            background-color: #ffebee;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Phone Number Lookup</h1>
        
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="lookupTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab">Single Lookup</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab">Bulk Lookup</button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3" id="lookupTabsContent">
                    <!-- Single Lookup Tab -->
                    <div class="tab-pane fade show active" id="single" role="tabpanel">
                        <form id="singleLookupForm">
                            @csrf
                            <div class="mb-3">
                                <label for="phoneNumber" class="form-label">Enter Phone Number</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phone_number" placeholder="e.g. +14158586273">
                            </div>
                            <button type="submit" class="btn btn-primary">Lookup</button>
                        </form>
                        
                        <div id="singleResult" class="mt-4" style="display: none;">
                            <!-- Single result will appear here -->
                        </div>
                    </div>
                    
                    <!-- Bulk Lookup Tab -->
                    <div class="tab-pane fade" id="bulk" role="tabpanel">
                        <form id="bulkLookupForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="fileUpload" class="form-label">Upload CSV/TXT File</label>
                                <input class="form-control" type="file" id="fileUpload" name="file" accept=".csv,.txt">
                                <div class="form-text">File should contain one phone number per line. Optional: add country code as second column (e.g., +1234567890,US)</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Process File</button>
                        </form>
                        
                        <div class="mt-4">
                            <div id="bulkResults" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Results</h5>
                                    <div class="form-group">
                                        <select id="perPageSelect" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                            <option value="5">5 per page</option>
                                            <option value="10">10 per page</option>
                                            <option value="20">20 per page</option>
                                            <option value="50">50 per page</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="table-responsive bulk-results">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Phone Number</th>
                                                <th>Type</th>
                                                <th>Carrier</th>
                                                <th>Location</th>
                                                <th>Country</th>
                                            </tr>
                                        </thead>
                                        <tbody id="resultsTableBody">
                                            <!-- Results will be inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <nav aria-label="Page navigation" class="mt-3">
                                    <ul class="pagination justify-content-center" id="pagination">
                                        <!-- Pagination will be inserted here -->
                                    </ul>
                                </nav>
                            </div>
                            
                            <div id="bulkErrors" class="mt-3" style="display: none;">
                                <h5>Errors</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Phone Number</th>
                                                <th>Error</th>
                                            </tr>
                                        </thead>
                                        <tbody id="errorsTableBody">
                                            <!-- Errors will be inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Result Template -->
    <template id="singleResultTemplate">
        <div class="card">
            <div class="card-header">
                Lookup Results
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Phone Number</th>
                        <td class="result-number"></td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td class="result-type"></td>
                    </tr>
                    <tr>
                        <th>Carrier</th>
                        <td class="result-carrier"></td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td class="result-location"></td>
                    </tr>
                    <tr>
                        <th>Country</th>
                        <td class="result-country"></td>
                    </tr>
                </table>
            </div>
        </div>
    </template>

    <!-- Result Row Template -->
    <template id="resultRowTemplate">
        <tr>
            <td class="phone-number"></td>
            <td class="type"></td>
            <td class="carrier"></td>
            <td class="location"></td>
            <td class="country"></td>
        </tr>
    </template>

    <!-- Error Row Template -->
    <template id="errorRowTemplate">
        <tr class="error-row">
            <td class="error-phone"></td>
            <td class="error-message"></td>
        </tr>
    </template>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize international telephone input
            const phoneInput = document.querySelector("#phoneNumber");
            const iti = window.intlTelInput(phoneInput, {
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                preferredCountries: ['us', 'gb', 'ca', 'au', 'de', 'fr'],
                separateDialCode: true,
                initialCountry: "auto",
                geoIpLookup: function(callback) {
                    fetch('https://ipapi.co/json/')
                        .then(res => res.json())
                        .then(data => callback(data.country_code))
                        .catch(() => callback('us'));
                }
            });

            // Single lookup form
            $('#singleLookupForm').submit(function(e) {
                e.preventDefault();
                
                const phoneNumber = iti.getNumber();
                if (!phoneNumber) {
                    alert('Please enter a valid phone number');
                    return;
                }

                $.ajax({
                    url: '/lookup',
                    type: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        phone_number: phoneNumber,
                        country_code: iti.getSelectedCountryData().iso2
                    },
                    // In your success callback for single lookup:
                    success: function(response) {
                        if (response.success) {
                            const template = $('#singleResultTemplate').html();
                            $('#singleResult').html(template).show();
                            
                            $('.result-number').text(response.data.phone_number);
                            $('.result-type').text(response.data.type || 'Unknown');
                            $('.result-carrier').text(response.data.carrier || 'Unknown');
                            $('.result-location').text(response.data.location || 'Unknown');
                            $('.result-country').text(response.data.country_name || 'Unknown');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                    }
                });
            });

            // Bulk lookup form
            $('#bulkLookupForm').submit(function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const perPage = $('#perPageSelect').val();
                
                $.ajax({
                    url: '/lookup?per_page=' + perPage,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            renderBulkResults(response);
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                    }
                });
            });

            // Per page change handler
            $('#perPageSelect').change(function() {
                const formData = new FormData($('#bulkLookupForm')[0]);
                const perPage = $(this).val();
                
                $.ajax({
                    url: '/lookup?per_page=' + perPage,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: renderBulkResults
                });
            });

            // Function to render bulk results
            function renderBulkResults(response) {
                const resultsTable = $('#resultsTableBody');
                const errorsTable = $('#errorsTableBody');
                const pagination = $('#pagination');
                
                // Clear previous results
                resultsTable.empty();
                errorsTable.empty();
                pagination.empty();
                
                // Render results
                if (response.data.length > 0) {
                    const rowTemplate = $('#resultRowTemplate').html();
                    
                    response.data.forEach(item => {
                        const $row = $(rowTemplate);
                        $row.find('.phone-number').text(item.phone_number);
                        $row.find('.type').text(item.type || 'Unknown');
                        $row.find('.carrier').text(item.carrier || 'Unknown');
                        $row.find('.location').text(item.location || 'Unknown');
                        $row.find('.country').text(item.country_name || 'Unknown');
                        resultsTable.append($row);
                    });
                    
                    $('#bulkResults').show();
                }
                
                // Render errors
                if (response.errors && response.errors.length > 0) {
                    const errorTemplate = $('#errorRowTemplate').html();
                    
                    response.errors.forEach(error => {
                        const $row = $(errorTemplate);
                        $row.find('.error-phone').text(error.phone);
                        $row.find('.error-message').text(error.error);
                        errorsTable.append($row);
                    });
                    
                    $('#bulkErrors').show();
                }
                
                // Render pagination
                if (response.pagination) {
                    const pagination = response.pagination;
                    
                    // Previous button
                    if (pagination.prev_page_url) {
                        pagination.append(`<li class="page-item">
                            <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                        </li>`);
                    } else {
                        pagination.append(`<li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>`);
                    }
                    
                    // Page numbers
                    for (let i = 1; i <= pagination.last_page; i++) {
                        pagination.append(`<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`);
                    }
                    
                    // Next button
                    if (pagination.next_page_url) {
                        pagination.append(`<li class="page-item">
                            <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
                        </li>`);
                    } else {
                        pagination.append(`<li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>`);
                    }
                    
                    // Pagination click handler
                    $('#pagination').on('click', '.page-link', function(e) {
                        e.preventDefault();
                        const page = $(this).data('page');
                        if (page) {
                            const formData = new FormData($('#bulkLookupForm')[0]);
                            const perPage = $('#perPageSelect').val();
                            
                            $.ajax({
                                url: `/lookup?page=${page}&per_page=${perPage}`,
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: renderBulkResults
                            });
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>