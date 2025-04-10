Here's a comprehensive `README.md` for your Phone Number Lookup project that documents all the functionality and setup:

```markdown
# Phone Number Lookup Service

A Laravel-based service for validating and looking up phone number information using the NumVerify API.

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)

## Features

- Single phone number validation
- Bulk phone number processing via CSV/TXT files
- Phone number normalization (handles multiple formats)
- Country code support
- Caching of lookup results
- Paginated bulk results
- Error handling and reporting
- Responsive web interface

## Requirements

- PHP 8.0+
- Laravel 9.x+
- Composer
- MySQL 5.7+ or equivalent database
- NumVerify API key

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/phone-lookup-service.git
   cd phone-lookup-service
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Create and configure `.env` file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure your database in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=phone_lookup
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Add your NumVerify API key:
   ```env
   NUMVERIFY_API_KEY=your_api_key_here
   ```

7. Run migrations:
   ```bash
   php artisan migrate
   ```

8. Install frontend dependencies (optional for development):
   ```bash
   npm install
   npm run dev
   ```

## Usage

### Web Interface

1. Start the development server:
   ```bash
   php artisan serve
   ```

2. Access the application at `http://localhost:8000`

### API Endpoints

- `POST /lookup` - Single phone number lookup
  ```json
  {
    "phone_number": "+1234567890",
    "country_code": "US" // optional
  }
  ```

- `POST /lookup` with file - Bulk phone number lookup

### Supported Phone Number Formats

The service supports multiple phone number formats:

1. International format:
   - `+13054564332`
   - `+442072193000`

2. Local format with country code:
   - `3054564332,US`
   - `2072193000,GB`

3. Local format (assumes US if 10 digits):
   - `3054564332` (automatically converted to +13054564332)

## Code Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── PhoneLookupController.php
├── Models/
│   └── PhoneLookup.php
├── Services/
│   └── PhoneLookupService.php
resources/
├── views/
│   └── phone-lookup.blade.php
routes/
└── web.php
```

## Configuration

The service can be configured via `.env`:

```env
# NumVerify API Settings
NUMVERIFY_API_KEY=your_api_key_here
NUMVERIFY_API_URL=http://apilayer.net/api/validate

# Cache Settings
PHONE_LOOKUP_CACHE_TTL=1440 # in minutes
```

## Dependencies

- [GuzzleHTTP](https://docs.guzzlephp.org/) - For API requests
- [intl-tel-input](https://intl-tel-input.com/) - For phone number input formatting
- [Bootstrap 5](https://getbootstrap.com/) - For frontend styling

## Error Handling

The service provides detailed error messages for:

- Invalid phone number formats
- API request failures
- File upload issues
- Database errors

## Testing

To run tests:

```bash
php artisan test
```

## Deployment

For production deployment:

1. Configure environment variables
2. Optimize Laravel:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. Set up queue workers if needed

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
```

This README includes:

1. Project overview and features
2. Installation instructions
3. Usage documentation
4. Supported phone number formats
5. Code structure explanation
6. Configuration options
7. Dependencies
8. Error handling information
9. Testing and deployment notes
10. Contribution guidelines
11. License information

You can customize any section further based on your specific requirements or additional features you may have implemented. The README is structured to be comprehensive yet easy to navigate for both developers and users.