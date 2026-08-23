<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\User;
use App\Models\Contact;
use App\Models\LeadStage;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Message;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Business
        $business = Business::firstOrCreate(
            ['name' => 'Acme Auto Solutions'],
            [
                'waba_id' => 'waba_987654321',
                'phone_number_id' => 'phone_123456789',
                'whatsapp_access_token' => 'EAAG_test_token',
                'meta_pixel_id' => '123456789012345',
                'capi_token' => 'EAAG_capi_test_token',
                'ai_provider' => 'openai',
                'timezone' => 'UTC',
                'currency' => 'INR'
            ]
        );

        // 2. Create Users (Admin, Sales Agent, Super Admin)
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@acme.com'],
            [
                'business_id' => $business->id,
                'name' => 'Acme Admin',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'rahul@acme.com'],
            [
                'business_id' => $business->id,
                'name' => 'Rahul Agent',
                'password' => bcrypt('password'),
                'role' => 'sales_agent'
            ]
        );

        User::updateOrCreate(
            ['email' => 'superadmin@wacrm.io'],
            [
                'name'        => 'Super Admin',
                'password'    => bcrypt('Admin@1234'),
                'role'        => 'super_admin',
                'business_id' => $business->id,
            ]
        );

        // 3. Create Lead Stages
        $stage1 = LeadStage::firstOrCreate(['business_id' => $business->id, 'name' => 'New Lead'], ['order_index' => 1, 'color' => '#3B82F6', 'mapped_meta_event' => 'Lead']);
        $stage2 = LeadStage::firstOrCreate(['business_id' => $business->id, 'name' => 'Qualified'], ['order_index' => 2, 'color' => '#10B981', 'mapped_meta_event' => 'QualifiedLead']);
        $stage3 = LeadStage::firstOrCreate(['business_id' => $business->id, 'name' => 'Quotation Sent'], ['order_index' => 3, 'color' => '#F59E0B', 'mapped_meta_event' => 'Proposal']);
        $stage4 = LeadStage::firstOrCreate(['business_id' => $business->id, 'name' => 'Won'], ['order_index' => 4, 'color' => '#059669', 'mapped_meta_event' => 'Purchase']);
        $stage5 = LeadStage::firstOrCreate(['business_id' => $business->id, 'name' => 'Lost'], ['order_index' => 5, 'color' => '#EF4444', 'mapped_meta_event' => null]);

        // 4. Create Sample Contact
        $contact = Contact::create([
            'business_id' => $business->id,
            'phone' => '+15550192834',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'company' => 'Apex Woodworks'
        ]);

        // 5. Create Lead
        $lead = Lead::create([
            'business_id' => $business->id,
            'contact_id' => $contact->id,
            'stage_id' => $stage2->id, // Qualified
            'assigned_user_id' => $user->id,
            'lead_score' => 85,
            'req_product' => 'CNC Router Machine X-500',
            'req_budget' => '$15,000',
            'req_timeline' => 'Within 14 Days',
            'expected_value' => 15000,
            'source' => 'Meta Ad Campaign',
            'campaign_name' => 'Summer CNC Router Promo',
            'fbc' => 'fb.1.16919283.9281',
            'fbp' => 'fb.1.16919283.8821'
        ]);

        // 6. Create Conversation & Messages
        $conversation = Conversation::create([
            'business_id' => $business->id,
            'contact_id' => $contact->id,
            'assigned_user_id' => $user->id,
            'status' => 'open',
            'ai_enabled' => true,
            'unread_count' => 0
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'contact',
            'type' => 'text',
            'content' => 'Hi, I saw your Meta Ad for the CNC Router Machine. I need one for furniture cutting within 2 weeks.',
            'status' => 'read'
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai',
            'type' => 'text',
            'content' => 'Hello John! Thank you for contacting Acme Auto Solutions. Our CNC Router X-500 is ideal for furniture cutting. May I know your estimated budget?',
            'status' => 'sent'
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'contact',
            'type' => 'text',
            'content' => 'My budget is around $15,000.',
            'status' => 'read'
        ]);
    }
}
