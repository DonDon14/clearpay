# Quick Development Reference

## 🎯 Two Environments

| Environment | Branch | Service Name | URL | Database |
|------------|--------|--------------|-----|----------|
| **Production** | `main` | `clearpay-web` | https://clearpay-web.onrender.com | `clearpay-db` |
| **Development** | `development` | `clearpay-web-dev` | https://clearpay-web-dev.onrender.com | `clearpay-db-dev` |

## 🚀 Quick Commands

### Switch to Development
```bash
git checkout development
```

### Switch to Production
```bash
git checkout main
```

### Deploy to Development Site
```bash
git checkout development
# Make your changes, then:
git add .
git commit -m "Your changes"
git push origin development
# Auto-deploys to https://clearpay-web-dev.onrender.com
```

### Deploy to Production Site
```bash
git checkout main
git merge development
git push origin main
# Auto-deploys to https://clearpay-web.onrender.com
```

## 📋 Workflow

1. **Work on `development` branch** → Test on dev site
2. **When ready** → Merge `development` → `main` → Deploys to production

## ⚠️ Important

- **Always develop on `development` branch first**
- **Test on dev site before merging to `main`**
- **Production and development use separate databases**
- **Production has real user data - be careful!**

## 📚 Full Guide

See `DEVELOPMENT_SETUP.md` for detailed instructions.

