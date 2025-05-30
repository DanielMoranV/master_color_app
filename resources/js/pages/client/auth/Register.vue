<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    type: 'persona',
    identity_document: '',
    type_document: 'DNI',
    phone: '',
});

const submit = () => {
    form.post(route('client.register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create a Client Account" description="Enter your details below to create your account">
        <Head title="Client Registration" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Full Name</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>
                
                <div class="grid gap-2">
                    <Label for="type">Client Type</Label>
                    <Select v-model="form.type" :tabindex="3">
                        <SelectTrigger>
                            <SelectValue placeholder="Select client type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="persona">Individual</SelectItem>
                            <SelectItem value="empresa">Business</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type" />
                </div>
                
                <div class="grid gap-2">
                    <Label for="type_document">Document Type</Label>
                    <Select v-model="form.type_document" :tabindex="4">
                        <SelectTrigger>
                            <SelectValue placeholder="Select document type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="DNI">DNI</SelectItem>
                            <SelectItem value="RUC">RUC</SelectItem>
                            <SelectItem value="CE">CE</SelectItem>
                            <SelectItem value="PASAPORTE">Passport</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type_document" />
                </div>
                
                <div class="grid gap-2">
                    <Label for="identity_document">Document Number</Label>
                    <Input id="identity_document" type="text" required :tabindex="5" v-model="form.identity_document" placeholder="Document number" />
                    <InputError :message="form.errors.identity_document" />
                </div>
                
                <div class="grid gap-2">
                    <Label for="phone">Phone Number</Label>
                    <Input id="phone" type="tel" required :tabindex="6" v-model="form.phone" placeholder="Phone number" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="8"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" :tabindex="9" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('client.login')" class="underline underline-offset-4" :tabindex="10">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
