#!/usr/bin/env bash
set -e

echo "🚀 Starting Orthoplex Laravel environment setup..."

# Build and start Docker containers
docker compose -f docker/docker-compose.yml up -d --build

echo "✅ Containers are up. Installing Composer dependencies..."
docker exec -it orthoplex_app composer install --no-interaction --prefer-dist

echo "🔑 Generating application key..."
docker exec -it orthoplex_app php artisan key:generate

echo "🗄️  Running migrations..."
docker exec -it orthoplex_app php artisan migrate --force

echo ""
echo "🎉 Setup complete!"
echo "🌐 Laravel App:     http://localhost:8001"
echo "📬 Mailpit:         http://localhost:8025"
echo "🗄️  phpMyAdmin:     http://localhost:8082"
echo "🐬 MySQL Host:      127.0.0.1 (port 3307, user: laravel, pass: secret)"
echo ""

