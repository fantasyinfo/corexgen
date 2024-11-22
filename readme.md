# CoreXGen

A Laravel-based web application generator.

## Prerequisites

Before you begin, ensure you have the following installed:
- PHP >= 8.1
- MYSQL
- Composer
- Node.js & NPM
- Git

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/fantasyinfo/corexgen.git
```

### 2. Navigate to Project Directory

```bash
cd corexgen
```

### 3. Set Up Environment File

Create a new .env file:
```bash
touch .env
```

Copy the contents from .env.example to .env:
```bash
cp .env.example .env
```

### 4. Install Dependencies

Install PHP dependencies:
```bash
composer update
```

Install Node.js dependencies:
```bash
npm install
```

### 5. Build Assets

Compile and build frontend assets:
```bash
npm run build
```

### 6. Start Development Server

Launch the Laravel development server:
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Configuration

After installation, you may need to:
1. Configure your database settings in the `.env` file
2. Generate application key: `php artisan key:generate`
3. Run migrations: `php artisan migrate`

## Development

For active development, you can use:
```bash
npm run dev
```

This will watch for changes in your assets and recompile them automatically.

## Troubleshooting

If you encounter any issues:

1. Check if all prerequisites are installed
2. Ensure all dependencies are installed correctly
3. Verify .env file configuration
4. Clear cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## Contributing

Please read our contributing guidelines before submitting pull requests.

## License

This project is licensed under the MIT License - see the LICENSE file for details.