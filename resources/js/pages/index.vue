<template>
  <div class="dashboard-container">
    <h1 class="dashboard-title">Dashboard</h1>
    <p class="dashboard-description">Welcome to Laravel Shopper Admin Panel</p>
    
    <!-- Dashboard Content -->
    <div class="dashboard-grid">
      <div class="dashboard-card">
        <h3>Quick Stats</h3>
        <p>Overview of your store performance</p>
      </div>
      
      <div class="dashboard-card">
        <h3>Recent Orders</h3>
        <p>Latest customer orders</p>
      </div>
      
      <div class="dashboard-card">
        <h3>Product Inventory</h3>
        <p>Stock levels and alerts</p>
      </div>
    </div>
  </div>
</template>

<script setup>
// This component will automatically receive the CpLayout
// Props will be passed from the Laravel controller
</script>

<style scoped>
.dashboard-container {
  padding: 2rem;
}

.dashboard-title {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.dashboard-description {
  color: #6b7280;
  margin-bottom: 2rem;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.dashboard-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.dashboard-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.dashboard-card p {
  color: #6b7280;
}
</style>