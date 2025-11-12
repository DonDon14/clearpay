#!/bin/bash
# Script to temporarily rename files for Render Blueprint creation

echo "🔧 Setting up render.yaml for development branch..."

# Check if we're on development branch
current_branch=$(git branch --show-current)
if [ "$current_branch" != "development" ]; then
    echo "⚠️  You're not on development branch. Current branch: $current_branch"
    echo "Switching to development branch..."
    git checkout development || {
        echo "❌ Failed to switch to development branch. Please create it first."
        exit 1
    }
fi

# Backup current render.yaml if it exists
if [ -f "render.yaml" ]; then
    echo "📦 Backing up render.yaml to render.prod.yaml..."
    mv render.yaml render.prod.yaml
fi

# Copy render.dev.yaml to render.yaml
if [ -f "render.dev.yaml" ]; then
    echo "📝 Copying render.dev.yaml to render.yaml..."
    cp render.dev.yaml render.yaml
    echo "✅ render.yaml is now configured for development"
else
    echo "❌ render.dev.yaml not found!"
    exit 1
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "Next steps:"
echo "1. Commit this change:"
echo "   git add render.yaml render.prod.yaml"
echo "   git commit -m 'Temporarily use render.dev.yaml as render.yaml for Blueprint'"
echo "   git push origin development"
echo ""
echo "2. Go to Render Dashboard and create Blueprint"
echo "   - It will now use render.yaml (which is the dev config)"
echo ""
echo "3. After Blueprint is created, restore files:"
echo "   git checkout development"
echo "   mv render.prod.yaml render.yaml"
echo "   git add render.yaml"
echo "   git commit -m 'Restore render.yaml structure'"
echo "   git push origin development"

