<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Trash2, ArrowLeft, ShoppingBag } from 'lucide-vue-next';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const cart = ref([]);
const loading = ref(false);

// Load cart from localStorage
onMounted(() => {
  const savedCart = localStorage.getItem('cart');
  if (savedCart) {
    cart.value = JSON.parse(savedCart);
  }
});

// Calculate cart totals
const subtotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

const tax = computed(() => {
  return subtotal.value * 0.18; // 18% tax
});

const total = computed(() => {
  return subtotal.value + tax.value;
});

// Update cart item quantity
const updateQuantity = (index, newQuantity) => {
  if (newQuantity < 1) newQuantity = 1;
  cart.value[index].quantity = newQuantity;
  saveCart();
};

// Remove item from cart
const removeItem = (index) => {
  cart.value.splice(index, 1);
  saveCart();
};

// Save cart to localStorage
const saveCart = () => {
  localStorage.setItem('cart', JSON.stringify(cart.value));
};

// Clear cart
const clearCart = () => {
  cart.value = [];
  saveCart();
};

// Checkout process
const checkout = () => {
  loading.value = true;
  
  // Check if user is authenticated
  const isAuthenticated = document.querySelector('meta[name="is-authenticated"]')?.getAttribute('content') === 'true';
  
  if (!isAuthenticated) {
    // Redirect to login page if not authenticated
    router.visit(route('client.login'), {
      preserveState: true,
      onSuccess: () => {
        loading.value = false;
      }
    });
    return;
  }
  
  // Submit order to backend
  router.post(route('client.orders.store'), {
    items: cart.value,
    subtotal: subtotal.value,
    tax: tax.value,
    total: total.value
  }, {
    preserveState: true,
    onSuccess: () => {
      clearCart();
      loading.value = false;
    },
    onError: () => {
      loading.value = false;
    }
  });
};
</script>

<template>
  <Head title="Shopping Cart" />
  
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Shopping Cart</h2>
    </template>
    
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div v-if="cart.length === 0" class="flex flex-col items-center justify-center py-12">
              <ShoppingBag class="mb-4 h-16 w-16 text-muted-foreground" />
              <h3 class="mb-2 text-xl font-medium">Your cart is empty</h3>
              <p class="mb-6 text-muted-foreground">Add some products to your cart to continue shopping</p>
              <Button asChild>
                <Link :href="route('store')">Continue Shopping</Link>
              </Button>
            </div>
            
            <div v-else>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead class="w-[100px]">Product</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead>Price</TableHead>
                    <TableHead>Quantity</TableHead>
                    <TableHead>Total</TableHead>
                    <TableHead class="w-[70px]"></TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="(item, index) in cart" :key="index">
                    <TableCell>
                      <img :src="item.image" :alt="item.name" class="h-16 w-16 rounded-md object-cover" />
                    </TableCell>
                    <TableCell class="font-medium">{{ item.name }}</TableCell>
                    <TableCell>${{ item.price.toFixed(2) }}</TableCell>
                    <TableCell>
                      <div class="flex w-24 items-center">
                        <Button 
                          variant="outline" 
                          size="icon" 
                          class="h-8 w-8" 
                          @click="updateQuantity(index, item.quantity - 1)"
                        >
                          -
                        </Button>
                        <Input 
                          type="number" 
                          class="mx-1 h-8 w-12 text-center" 
                          :value="item.quantity"
                          @change="e => updateQuantity(index, parseInt(e.target.value))"
                          min="1"
                        />
                        <Button 
                          variant="outline" 
                          size="icon" 
                          class="h-8 w-8" 
                          @click="updateQuantity(index, item.quantity + 1)"
                        >
                          +
                        </Button>
                      </div>
                    </TableCell>
                    <TableCell class="font-medium">${{ (item.price * item.quantity).toFixed(2) }}</TableCell>
                    <TableCell>
                      <Button variant="ghost" size="icon" @click="removeItem(index)">
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
              
              <div class="mt-8 flex flex-col items-end">
                <div class="w-full space-y-2 md:w-72">
                  <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>${{ subtotal.toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Tax (18%)</span>
                    <span>${{ tax.toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between border-t pt-2 text-lg font-bold">
                    <span>Total</span>
                    <span>${{ total.toFixed(2) }}</span>
                  </div>
                </div>
              </div>
              
              <div class="mt-8 flex justify-between">
                <Button asChild variant="outline">
                  <Link :href="route('store')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Continue Shopping
                  </Link>
                </Button>
                
                <div class="space-x-2">
                  <Button variant="outline" @click="clearCart">Clear Cart</Button>
                  <Button @click="checkout" :disabled="loading">
                    <span v-if="loading" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                    Checkout
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
