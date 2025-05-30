<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ShoppingCart, Search } from 'lucide-vue-next';
import axios from 'axios';

const products = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const selectedCategory = ref('all');
const sortBy = ref('name');
const cart = ref([]);

// Load products from API
onMounted(async () => {
  try {
    const response = await axios.get('/api/products');
    products.value = response.data;
    loading.value = false;
    
    // Load cart from localStorage
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
      cart.value = JSON.parse(savedCart);
    }
  } catch (error) {
    console.error('Error loading products:', error);
    loading.value = false;
  }
});

// Filter products based on search and category
const filteredProducts = computed(() => {
  let result = products.value;
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(product => 
      product.name.toLowerCase().includes(query) || 
      product.description.toLowerCase().includes(query) ||
      product.brand.toLowerCase().includes(query)
    );
  }
  
  // Filter by category (assuming products have a category field)
  if (selectedCategory.value !== 'all') {
    result = result.filter(product => product.brand === selectedCategory.value);
  }
  
  // Sort products
  if (sortBy.value === 'name') {
    result.sort((a, b) => a.name.localeCompare(b.name));
  } else if (sortBy.value === 'price_low') {
    result.sort((a, b) => a.stock.sale_price - b.stock.sale_price);
  } else if (sortBy.value === 'price_high') {
    result.sort((a, b) => b.stock.sale_price - a.stock.sale_price);
  }
  
  return result;
});

// Get unique brands for category filter
const categories = computed(() => {
  const brands = new Set();
  products.value.forEach(product => brands.add(product.brand));
  return Array.from(brands);
});

// Add product to cart
const addToCart = (product) => {
  const existingItem = cart.value.find(item => item.id === product.id);
  
  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      price: product.stock.sale_price,
      image: product.image_url,
      quantity: 1
    });
  }
  
  // Save cart to localStorage
  localStorage.setItem('cart', JSON.stringify(cart.value));
};

// Calculate total items in cart
const cartItemCount = computed(() => {
  return cart.value.reduce((total, item) => total + item.quantity, 0);
});
</script>

<template>
  <Head title="Online Store" />
  
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="mx-auto flex max-w-7xl items-center justify-between p-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-primary">Master Color Store</h1>
        
        <div class="flex items-center space-x-4">
          <Button asChild variant="outline">
            <Link :href="route('client.login')">Login</Link>
          </Button>
          
          <Button asChild variant="outline" class="relative">
            <Link :href="route('client.cart')">
              <ShoppingCart class="h-5 w-5" />
              <span v-if="cartItemCount > 0" class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-xs text-white">
                {{ cartItemCount }}
              </span>
            </Link>
          </Button>
        </div>
      </div>
    </header>
    
    <!-- Main Content -->
    <main class="mx-auto max-w-7xl p-4 sm:px-6 lg:px-8">
      <!-- Search and Filters -->
      <div class="mb-8 mt-6 grid gap-4 md:grid-cols-3">
        <div class="relative">
          <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            placeholder="Search products..."
            class="pl-9"
          />
        </div>
        
        <Select v-model="selectedCategory">
          <SelectTrigger>
            <SelectValue placeholder="Category" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Categories</SelectItem>
            <SelectItem v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </SelectItem>
          </SelectContent>
        </Select>
        
        <Select v-model="sortBy">
          <SelectTrigger>
            <SelectValue placeholder="Sort by" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="name">Name (A-Z)</SelectItem>
            <SelectItem value="price_low">Price (Low to High)</SelectItem>
            <SelectItem value="price_high">Price (High to Low)</SelectItem>
          </SelectContent>
        </Select>
      </div>
      
      <!-- Products Grid -->
      <div v-if="loading" class="flex h-64 items-center justify-center">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
      </div>
      
      <div v-else-if="filteredProducts.length === 0" class="flex h-64 flex-col items-center justify-center">
        <p class="text-xl font-medium">No products found</p>
        <p class="text-muted-foreground">Try adjusting your search or filter criteria</p>
      </div>
      
      <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        <Card v-for="product in filteredProducts" :key="product.id" class="overflow-hidden">
          <img :src="product.image_url" :alt="product.name" class="h-48 w-full object-cover" />
          
          <CardHeader class="pb-2">
            <CardTitle class="line-clamp-1 text-lg">{{ product.name }}</CardTitle>
            <CardDescription class="line-clamp-1">{{ product.brand }}</CardDescription>
          </CardHeader>
          
          <CardContent class="space-y-2 pb-2">
            <p class="text-xl font-bold text-primary">${{ product.stock.sale_price.toFixed(2) }}</p>
            <p v-if="product.stock.quantity > 0" class="text-sm text-green-600">In Stock ({{ product.stock.quantity }})</p>
            <p v-else class="text-sm text-red-600">Out of Stock</p>
          </CardContent>
          
          <CardFooter>
            <Button 
              @click="addToCart(product)" 
              class="w-full" 
              :disabled="product.stock.quantity <= 0"
            >
              Add to Cart
            </Button>
          </CardFooter>
        </Card>
      </div>
    </main>
    
    <!-- Footer -->
    <footer class="mt-12 bg-white py-6 shadow-inner">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0">
          <p class="text-sm text-muted-foreground">© 2025 Master Color Store. All rights reserved.</p>
          
          <div class="flex space-x-4">
            <Link href="#" class="text-sm text-muted-foreground hover:text-primary">About Us</Link>
            <Link href="#" class="text-sm text-muted-foreground hover:text-primary">Contact</Link>
            <Link href="#" class="text-sm text-muted-foreground hover:text-primary">Terms</Link>
            <Link href="#" class="text-sm text-muted-foreground hover:text-primary">Privacy</Link>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
