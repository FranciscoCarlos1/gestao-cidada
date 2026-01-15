<template>
  <div class="grid">
    <section class="card">
      <h2>Bem-vindo</h2>
      <p class="muted">
        Este é o front-end (Vue 3) do projeto Gestão Cidadã, integrado à API Laravel.
      </p>
      <div class="row">
        <RouterLink class="btn" to="/novo">Abrir solicitação</RouterLink>
        <RouterLink class="btn secondary" to="/problemas">Ver solicitações públicas</RouterLink>
      </div>
    </section>

    <section class="card">
      <h3>Perfis</h3>
      <ul class="muted">
        <li><b>Cidadão</b>: abre e acompanha solicitações</li>
        <li><b>Admin/Prefeitura</b>: gerencia solicitações e status</li>
        <li><b>Super Admin</b>: administra usuários, prefeituras e auditoria</li>
      </ul>
    </section>

    <!-- Bloco de acesso rápido removido a pedido -->
  </div>
</template>
