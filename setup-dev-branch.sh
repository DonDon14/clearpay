#!/bin/bash
# Script to set up development branch for ClearPay

echo "🚀 Setting up development branch..."

# Check if we're on main branch
current_branch=$(git branch --show-current)
if [ "$current_branch" != "main" ]; then
    echo "⚠️  Warning: You're not on main branch. Current branch: $current_branch"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo "⚠️  You have uncommitted changes. Please commit or stash them first."
    exit 1
fi

# Create development branch from current branch
echo "📝 Creating development branch..."
git checkout -b development

# Push development branch to remote
echo "📤 Pushing development branch to remote..."
git push -u origin development

echo ""
echo "✅ Development branch created and pushed!"
echo ""
echo "Next steps:"
echo "1. Go to Render Dashboard: https://dashboard.render.com"
echo "2. Click 'New +' → 'Blueprint'"
echo "3. Select 'render.dev.yaml' as the blueprint file"
echo "4. Click 'Apply'"
echo ""
echo "This will create:"
echo "  - Development service: clearpay-web-dev"
echo "  - Development database: clearpay-db-dev"
echo "  - URL: https://clearpay-web-dev.onrender.com"
echo ""
echo "Current branches:"
echo "  - main (production) → https://clearpay-web.onrender.com"
echo "  - development (staging) → https://clearpay-web-dev.onrender.com"

