# create-portal

Create a new Vue 3 portal inside `resources/js/{portal-name}`.

The portal must follow a modular and scalable structure for a Laravel + Vue project.

Use Vue 3, Composition API, and `<script setup>`.

## Rules

- Portal folder name must be lowercase and kebab-case if needed.
- Do not overwrite existing files unless explicitly asked.
- Match the existing project structure and coding style.
- Keep components small, reusable, and clean.
- Use relative imports or existing alias conventions already used in the project.

## Create Portal Folder

Create:

`resources/js/{portal-name}/`

## Required Structure

Create the following inside the portal:

- `pages/`
- `components/`
- `layouts/`
- `composables/`
- `services/`
- `router/`
- `stores/`
- `assets/`

Also create:

- `App.vue`
- `main.js`

## Starter Pages

Create:

- `pages/DashboardPage.vue`

This should be a simple starter dashboard page for the portal.

## Layout

Create:

- `layouts/MainLayout.vue`

The layout should include:

- sidebar area
- header area
- main content area
- router view or slot for page content

Keep the layout clean and reusable.

## Router

Create:

- `router/index.js`

The router should:

- use Vue Router
- register a dashboard route
- use the project’s existing routing style
- use history mode if that is already the project standard

Example route:

- dashboard → `DashboardPage.vue`

## Store

Create:

- `stores/appStore.js`

Use the project's existing store pattern.  
If the project uses Pinia, create a simple Pinia store.  
If not, create a minimal placeholder store file.

## App.vue

Create a root portal app component.

It should:

- use the main layout
- render router content
- remain minimal and clean

## main.js

Initialize the Vue app for this portal.

Register:

- router
- store if used by the project
- any shared global setup already used in the codebase

Mount to:

`#app`

If the project already uses a different mount selector, follow the existing project convention instead of forcing a new one.

## Output

Return:

- created folders
- created files
- brief explanation of portal structure

## Example Usage

`/create-portal admin`

This should create:

`resources/js/admin/`

with all required starter files and folders.

This command will be available in chat with `/create-portal`