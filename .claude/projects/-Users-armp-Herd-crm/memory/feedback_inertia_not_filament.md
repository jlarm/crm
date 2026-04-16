---
name: Build on Inertia side, not Filament
description: User wants all new features built on the Inertia/Vue side, not Filament admin panel
type: feedback
---

Always build new features on the Inertia side of the application, NOT in Filament.

**Why:** The user has had to correct this multiple times and is frustrated. Filament is for the admin panel only. New user-facing features go in the Inertia/Vue layer.

**How to apply:** Before building any page, resource, widget, or dashboard feature, check for the Inertia/Vue structure (resources/js, routes/web.php controllers) and build there. Never default to Filament for new features.
