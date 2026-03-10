<template>
  <div class="flex min-h-screen items-center justify-center bg-zinc-50 p-4 dark:bg-zinc-950">
    <div class="w-full max-w-sm">
      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
          Student sign in
        </h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
          Sign in with your Student ID or email.
        </p>

        <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="error"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ error }}
          </p>

          <div>
            <label for="login" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Student ID / Email</label>
            <input
              id="login"
              v-model="login"
              type="text"
              autocomplete="username"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              :disabled="loading"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password</label>
            <input
              id="password"
              v-model="password"
              type="password"
              autocomplete="current-password"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              :disabled="loading"
            />
          </div>

          <div class="flex items-center">
            <input
              id="remember"
              v-model="remember"
              type="checkbox"
              class="h-4 w-4 rounded border-zinc-300 text-zinc-600 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
            />
            <label for="remember" class="ml-2 text-sm text-zinc-600 dark:text-zinc-400">Remember me</label>
          </div>

          <button
            type="submit"
            class="w-full rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            :disabled="loading"
          >
            <span v-if="loading">Signing in…</span>
            <span v-else>Sign in</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useStudentAuth } from '../composables/useStudentAuth.js';

const router = useRouter();
const { login: doLogin, loading, error } = useStudentAuth();
const login = ref('');
const password = ref('');
const remember = ref(false);

async function handleSubmit() {
  error.value = null;
  try {
    await doLogin({ login: login.value, password: password.value, remember: remember.value });
    const redirect = router.currentRoute.value.query.redirect;
    router.push(redirect || '/student/dashboard');
  } catch {
    // error set in composable
  }
}
</script>
