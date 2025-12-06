# AI-Powered F&B Features & Charging Model
## Recipe Generation, Cooking Instructions & Nutrition Facts

---

## Executive Summary

This document outlines AI-powered features for F&B companies to auto-generate recipes, cooking instruction photos/videos, and nutrition facts using OpenRouter. Includes comprehensive charging model for AI usage.

---

## 1. AI FEATURES OVERVIEW

### 1.1 AI-Powered Features

**Feature 1: AI Recipe Generation**
- Generate complete recipes from product ingredients
- Create recipe variations
- Suggest complementary dishes
- Generate recipe descriptions

**Feature 2: AI Cooking Instructions**
- Generate step-by-step cooking instructions
- Create cooking instruction photos (AI-generated)
- Create cooking instruction videos (AI-generated)
- Focus on clear, visual steps

**Feature 3: AI Nutrition Facts**
- Auto-calculate nutrition facts from ingredients
- Generate nutrition labels
- Calculate calories, macros, vitamins
- Compliance with food labeling regulations

---

## 2. AI RECIPE GENERATION

### 2.1 Recipe Generation Flow

```
User Input:
├── Product/Ingredients list
├── Cuisine type (optional)
├── Dietary preferences (optional)
├── Serving size (optional)
└── Difficulty level (optional)

AI Processing:
├── Analyze ingredients
├── Generate recipe name
├── Generate recipe description
├── Generate ingredients list
├── Generate cooking steps
├── Generate cooking time
├── Generate serving size
└── Generate tips/variations

Output:
├── Complete recipe
├── Recipe images (AI-generated)
└── Recipe metadata
```

### 2.2 Recipe Generation Options

**Option 1: From Product Ingredients**
- Select products from catalog
- AI generates recipe using those products
- Example: Select "Chicken", "Tomatoes", "Pasta" → Generate "Chicken Pasta Recipe"

**Option 2: From Text Description**
- User describes what they want
- AI generates recipe
- Example: "Italian pasta dish with chicken" → Generate recipe

**Option 3: Recipe Variations**
- Start with existing recipe
- AI generates variations
- Example: "Make this vegetarian" → Generate vegetarian version

**Option 4: Complementary Recipes**
- Based on main product
- AI suggests complementary dishes
- Example: "Burger" → Suggest "Fries", "Onion Rings", "Milkshake"

### 2.3 Database Schema

```sql
-- AI Generated Recipes
ai_recipes
├── id
├── company_id (foreign key)
├── product_id (foreign key, nullable) - If recipe is for specific product
├── recipe_name (string)
├── recipe_description (text)
├── cuisine_type (string, nullable)
├── difficulty_level (enum: easy, medium, hard)
├── prep_time_minutes (integer)
├── cook_time_minutes (integer)
├── total_time_minutes (integer)
├── serving_size (string) - "2-3 people"
├── calories_per_serving (integer, nullable)
├── ingredients (json) - [{name, quantity, unit, notes}]
├── instructions (json) - [{step_number, instruction, image_url, video_url}]
├── tips (text, nullable)
├── variations (text, nullable)
├── dietary_tags (json) - ["vegetarian", "gluten-free"]
├── ai_model_used (string) - Which AI model generated this
├── ai_tokens_used (integer) - Token usage
├── ai_cost (decimal) - Cost of generation
├── generated_at (datetime)
├── is_approved (boolean, default: false)
├── approved_by (foreign key, nullable)
├── approved_at (datetime, nullable)
└── timestamps

-- AI Recipe Images
ai_recipe_images
├── id
├── recipe_id (foreign key)
├── image_type (enum: hero, step, final_result)
├── step_number (integer, nullable) - If step image
├── image_url (string)
├── image_prompt (text) - Prompt used to generate
├── ai_model_used (string)
├── ai_cost (decimal)
└── timestamps

-- AI Recipe Videos
ai_recipe_videos
├── id
├── recipe_id (foreign key)
├── step_number (integer, nullable) - If step video
├── video_url (string)
├── video_duration (integer) - Seconds
├── video_prompt (text) - Prompt used to generate
├── ai_model_used (string)
├── ai_cost (decimal)
└── timestamps
```

---

## 3. AI COOKING INSTRUCTIONS

### 3.1 Cooking Instructions Generation

**Focus: Step-by-Step Visual Instructions**

**Input:**
- Recipe steps (text)
- Desired visual style
- Focus areas (e.g., "focus on knife skills", "show temperature")

**Output:**
- Step-by-step photos (AI-generated)
- Step-by-step videos (AI-generated)
- Clear, instructional visuals

### 3.2 Cooking Instruction Types

**Type 1: Step Photos**
- AI generates photo for each cooking step
- Focus on the action/technique
- Clear, instructional images

**Type 2: Step Videos**
- AI generates short video for each step
- Shows the process/movement
- 5-15 seconds per step

**Type 3: Full Process Video**
- AI generates complete cooking video
- Shows entire recipe preparation
- 1-3 minutes total

### 3.3 Visual Focus Areas

**Focus Options:**
- **Knife Skills** - Chopping, dicing, slicing
- **Temperature Control** - Heat levels, timing
- **Mixing Techniques** - Stirring, folding, whisking
- **Plating** - Presentation, garnishing
- **Safety** - Proper handling, precautions

### 3.4 Database Schema

```sql
-- Already covered in ai_recipes table (instructions field)
-- Additional table for instruction media
ai_instruction_media
├── id
├── recipe_id (foreign key)
├── step_number (integer)
├── media_type (enum: image, video)
├── media_url (string)
├── media_prompt (text)
├── focus_area (string, nullable) - "knife_skills", "temperature"
├── ai_model_used (string)
├── ai_cost (decimal)
└── timestamps
```

---

## 4. AI NUTRITION FACTS

### 4.1 Nutrition Facts Generation

**Input:**
- Ingredients list with quantities
- Serving size
- Recipe yield

**AI Processing:**
- Analyze each ingredient
- Calculate total nutrition per serving
- Generate nutrition label
- Ensure compliance with regulations

**Output:**
- Complete nutrition facts label
- Calories, macros, vitamins, minerals
- Allergen information
- Compliance-ready format

### 4.2 Nutrition Facts Components

**Required Information:**
- Serving size
- Servings per container
- Calories
- Total fat, saturated fat, trans fat
- Cholesterol
- Sodium
- Total carbohydrates, dietary fiber, sugars
- Protein
- Vitamins (A, C, D, E, K, B vitamins)
- Minerals (Calcium, Iron, Potassium, etc.)

**Optional Information:**
- % Daily Value
- Allergen warnings
- Health claims
- Country-specific requirements (US, EU, Singapore, etc.)

### 4.3 Database Schema

```sql
-- AI Generated Nutrition Facts
ai_nutrition_facts
├── id
├── product_id (foreign key)
├── recipe_id (foreign key, nullable)
├── serving_size (string) - "1 serving (200g)"
├── servings_per_container (integer)
├── calories (integer)
├── total_fat_g (decimal)
├── saturated_fat_g (decimal)
├── trans_fat_g (decimal)
├── cholesterol_mg (integer)
├── sodium_mg (integer)
├── total_carbohydrates_g (decimal)
├── dietary_fiber_g (decimal)
├── sugars_g (decimal)
├── protein_g (decimal)
├── vitamin_a_iu (integer, nullable)
├── vitamin_c_mg (decimal, nullable)
├── calcium_mg (integer, nullable)
├── iron_mg (decimal, nullable)
├── potassium_mg (integer, nullable)
├── other_vitamins (json, nullable) - Additional vitamins
├── other_minerals (json, nullable) - Additional minerals
├── allergens (json) - Allergen list
├── nutrition_label_image (string, nullable) - Generated label image
├── ai_model_used (string)
├── ai_tokens_used (integer)
├── ai_cost (decimal)
├── compliance_standard (string) - "US", "EU", "SG"
├── generated_at (datetime)
├── is_verified (boolean, default: false)
├── verified_by (foreign key, nullable)
├── verified_at (datetime, nullable)
└── timestamps
```

---

## 5. OPENROUTER INTEGRATION

### 5.1 OpenRouter Overview

**What is OpenRouter?**
- Unified API for multiple AI models
- Access to GPT-4, Claude, Gemini, etc.
- Cost-effective routing
- Usage tracking

### 5.2 Supported AI Models

**For Recipe Generation:**
- GPT-4 Turbo (text generation)
- Claude 3 Opus (detailed recipes)
- Gemini Pro (creative variations)

**For Image Generation:**
- DALL-E 3 (high-quality food images)
- Midjourney (via API, if available)
- Stable Diffusion XL (cost-effective)

**For Video Generation:**
- Runway Gen-2 (cooking videos)
- Pika Labs (short videos)
- Kling AI (food videos)

**For Nutrition Facts:**
- GPT-4 Turbo (calculation)
- Claude 3 Sonnet (accurate data)
- Specialized nutrition APIs

### 5.3 OpenRouter Configuration

```php
// config/openrouter.php
return [
    'api_key' => env('OPENROUTER_API_KEY'),
    'base_url' => 'https://openrouter.ai/api/v1',
    
    'models' => [
        'recipe_generation' => env('OPENROUTER_RECIPE_MODEL', 'openai/gpt-4-turbo'),
        'image_generation' => env('OPENROUTER_IMAGE_MODEL', 'openai/dall-e-3'),
        'video_generation' => env('OPENROUTER_VIDEO_MODEL', 'runway/gen-2'),
        'nutrition_facts' => env('OPENROUTER_NUTRITION_MODEL', 'anthropic/claude-3-sonnet'),
    ],
    
    'defaults' => [
        'max_tokens' => 2000,
        'temperature' => 0.7,
    ],
];
```

### 5.4 OpenRouter Service

```php
namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class OpenRouterService
{
    protected $apiKey;
    protected $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = config('openrouter.api_key');
        $this->baseUrl = config('openrouter.base_url');
    }
    
    /**
     * Generate recipe using AI
     */
    public function generateRecipe($ingredients, $options = [])
    {
        $model = config('openrouter.models.recipe_generation');
        
        $prompt = $this->buildRecipePrompt($ingredients, $options);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => 'Craveva Recipe Generator',
        ])->post("{$this->baseUrl}/chat/completions", [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional chef and recipe writer.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7,
        ]);
        
        $data = $response->json();
        
        return [
            'recipe' => $this->parseRecipeResponse($data['choices'][0]['message']['content']),
            'tokens_used' => $data['usage']['total_tokens'],
            'cost' => $this->calculateCost($model, $data['usage']),
        ];
    }
    
    /**
     * Generate cooking instruction image
     */
    public function generateInstructionImage($step, $focusArea = null)
    {
        $model = config('openrouter.models.image_generation');
        
        $prompt = $this->buildImagePrompt($step, $focusArea);
        
        // Use DALL-E or similar for image generation
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->baseUrl}/images/generations", [
            'model' => $model,
            'prompt' => $prompt,
            'size' => '1024x1024',
            'quality' => 'hd',
        ]);
        
        $data = $response->json();
        
        return [
            'image_url' => $data['data'][0]['url'],
            'cost' => $this->calculateImageCost($model),
        ];
    }
    
    /**
     * Generate nutrition facts
     */
    public function generateNutritionFacts($ingredients, $servingSize)
    {
        $model = config('openrouter.models.nutrition_facts');
        
        $prompt = $this->buildNutritionPrompt($ingredients, $servingSize);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->baseUrl}/chat/completions", [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a nutritionist expert. Calculate accurate nutrition facts.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);
        
        $data = $response->json();
        
        return [
            'nutrition_facts' => $this->parseNutritionResponse($data['choices'][0]['message']['content']),
            'tokens_used' => $data['usage']['total_tokens'],
            'cost' => $this->calculateCost($model, $data['usage']),
        ];
    }
    
    /**
     * Calculate cost based on model and usage
     */
    protected function calculateCost($model, $usage)
    {
        // OpenRouter pricing varies by model
        // Get pricing from OpenRouter API or config
        $pricing = $this->getModelPricing($model);
        
        $inputCost = ($usage['prompt_tokens'] / 1000) * $pricing['input'];
        $outputCost = ($usage['completion_tokens'] / 1000) * $pricing['output'];
        
        return $inputCost + $outputCost;
    }
}
```

---

## 6. CHARGING MODEL

### 6.1 Charging Model Options

**Option 1: Pay-Per-Use (PPU)**
- Charge per AI generation
- Transparent pricing
- Pay only for what you use

**Option 2: Subscription Tiers**
- Monthly subscription with AI credits
- Different tiers with different limits
- Overage charges

**Option 3: Hybrid Model** (Recommended)
- Base subscription with included credits
- Pay-per-use for additional usage
- Best of both worlds

### 6.2 Recommended Charging Model: Hybrid

**Tier Structure:**

```
┌─────────────────────────────────────────────────────────┐
│  TIER 1: STARTER                                        │
│  Monthly Fee: $29/month                                 │
│  Included AI Credits: 100 credits/month                │
│  Overage: $0.10 per credit                              │
│  Features:                                               │
│  • Recipe generation (5 credits)                        │
│  • Nutrition facts (3 credits)                          │
│  • Basic images (2 credits)                             │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  TIER 2: PROFESSIONAL                                   │
│  Monthly Fee: $99/month                                 │
│  Included AI Credits: 500 credits/month                 │
│  Overage: $0.08 per credit                              │
│  Features:                                               │
│  • Recipe generation (5 credits)                        │
│  • Nutrition facts (3 credits)                          │
│  • High-quality images (3 credits)                      │
│  • Step videos (10 credits)                            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  TIER 3: ENTERPRISE                                     │
│  Monthly Fee: $299/month                                │
│  Included AI Credits: 2,000 credits/month              │
│  Overage: $0.05 per credit                              │
│  Features:                                               │
│  • Unlimited recipe generation                          │
│  • Unlimited nutrition facts                            │
│  • Premium images & videos                              │
│  • Priority support                                     │
│  • Custom AI models                                     │
└─────────────────────────────────────────────────────────┘
```

### 6.3 Credit System

**Credit Costs:**

| Feature | Credits | Cost (at $0.10/credit) |
|---------|---------|------------------------|
| Recipe Generation (Basic) | 5 | $0.50 |
| Recipe Generation (Advanced) | 8 | $0.80 |
| Nutrition Facts (Basic) | 3 | $0.30 |
| Nutrition Facts (Detailed) | 5 | $0.50 |
| Instruction Image (Basic) | 2 | $0.20 |
| Instruction Image (HD) | 3 | $0.30 |
| Step Video (15 sec) | 10 | $1.00 |
| Full Recipe Video (2 min) | 30 | $3.00 |
| Recipe Variation | 3 | $0.30 |
| Complementary Recipe | 2 | $0.20 |

### 6.4 Cost Calculation

**Example:**
- Company generates 20 recipes/month
- Each recipe: 5 credits (generation) + 3 credits (nutrition) + 6 credits (3 images) = 14 credits
- Total: 20 × 14 = 280 credits/month

**Starter Tier:**
- Included: 100 credits
- Overage: 180 credits × $0.10 = $18
- Total: $29 + $18 = $47/month

**Professional Tier:**
- Included: 500 credits
- Overage: 0 credits
- Total: $99/month (better value!)

### 6.5 Database Schema for Charging

```sql
-- AI Subscription Tiers
ai_subscription_tiers
├── id
├── tier_name (string) - "Starter", "Professional", "Enterprise"
├── monthly_fee (decimal)
├── included_credits (integer)
├── overage_rate_per_credit (decimal)
├── features (json) - Available features
├── is_active (boolean)
└── timestamps

-- Company AI Subscriptions
company_ai_subscriptions
├── id
├── company_id (foreign key)
├── tier_id (foreign key)
├── subscription_start_date (date)
├── subscription_end_date (date, nullable)
├── status (enum: active, cancelled, expired)
├── current_credits (integer) - Remaining credits this month
├── credits_reset_date (date) - When credits reset
└── timestamps

-- AI Usage Tracking
ai_usage_logs
├── id
├── company_id (foreign key)
├── user_id (foreign key, nullable) - Who used it
├── feature_type (enum: recipe, nutrition, image, video)
├── feature_details (json) - What was generated
├── credits_used (integer)
├── cost (decimal) - Actual cost
├── ai_model_used (string)
├── tokens_used (integer, nullable)
├── generated_at (datetime)
└── timestamps

-- AI Billing
ai_billing_records
├── id
├── company_id (foreign key)
├── subscription_id (foreign key)
├── billing_period_start (date)
├── billing_period_end (date)
├── subscription_fee (decimal)
├── included_credits (integer)
├── credits_used (integer)
├── overage_credits (integer)
├── overage_cost (decimal)
├── total_cost (decimal)
├── status (enum: pending, paid, failed)
├── invoice_id (foreign key, nullable)
└── timestamps
```

---

## 7. SUPERADMIN AI SETTINGS

### 7.1 SuperAdmin Configuration

**Settings Needed:**

```php
// SuperAdmin can configure:
- OpenRouter API credentials
- Default AI models for each feature
- Credit costs per feature
- Subscription tier pricing
- Usage limits
- AI model availability
- Cost markup (platform profit margin)
```

### 7.2 Database Schema

```sql
-- AI Configuration Settings
ai_configuration_settings
├── id
├── setting_key (string, unique)
├── setting_value (text) - JSON or text
├── setting_type (enum: api_key, model_config, pricing, limits)
├── description (text)
└── timestamps

-- Example settings:
- openrouter_api_key (encrypted)
- default_recipe_model
- default_image_model
- default_video_model
- default_nutrition_model
- credit_cost_recipe_generation
- credit_cost_nutrition_facts
- credit_cost_image_generation
- credit_cost_video_generation
- platform_markup_percentage
- max_credits_per_month_per_company
```

### 7.3 SuperAdmin UI

```
┌─────────────────────────────────────────────────────────┐
│  AI Configuration - SuperAdmin                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  OpenRouter Settings:                                   │
│  API Key: [________________] [Test Connection]         │
│                                                          │
│  Default AI Models:                                      │
│  Recipe Generation: [GPT-4 Turbo ▼]                    │
│  Image Generation: [DALL-E 3 ▼]                        │
│  Video Generation: [Runway Gen-2 ▼]                    │
│  Nutrition Facts: [Claude 3 Sonnet ▼]                  │
│                                                          │
│  Credit Pricing:                                        │
│  Recipe Generation: [5] credits                        │
│  Nutrition Facts: [3] credits                          │
│  Instruction Image: [2] credits                         │
│  Step Video: [10] credits                              │
│                                                          │
│  Subscription Tiers:                                   │
│  [Manage Tiers]                                         │
│                                                          │
│  Platform Markup: [20]%                                │
│                                                          │
│  Usage Limits:                                          │
│  Max Credits/Month/Company: [10,000]                    │
│                                                          │
│  [Save Configuration]                                   │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 8. USER INTERFACE

### 8.1 Recipe Generation UI

```
┌─────────────────────────────────────────────────────────┐
│  🤖 AI Recipe Generator                                  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Select Products/Ingredients:                            │
│  [Search Products...]                                    │
│  ✅ Chicken (500g)                                       │
│  ✅ Tomatoes (200g)                                      │
│  ✅ Pasta (300g)                                         │
│  [+ Add More]                                            │
│                                                          │
│  Recipe Options:                                         │
│  Cuisine Type: [Italian ▼]                              │
│  Difficulty: [Medium ▼]                                 │
│  Serving Size: [2-3 people ▼]                          │
│  Dietary: [☐ Vegetarian] [☐ Gluten-Free]               │
│                                                          │
│  Generate Options:                                       │
│  ☑ Generate Recipe                                      │
│  ☑ Generate Nutrition Facts                             │
│  ☑ Generate Cooking Images                              │
│  ☑ Generate Step Videos                                  │
│                                                          │
│  Estimated Cost: 14 credits ($1.40)                      │
│  Your Credits: 250 remaining                            │
│                                                          │
│  [Generate Recipe]                                       │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 8.2 Generated Recipe Display

```
┌─────────────────────────────────────────────────────────┐
│  🍝 Chicken Pasta Recipe (AI Generated)                 │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  [AI Generated Hero Image]                              │
│                                                          │
│  Description:                                            │
│  A delicious Italian pasta dish with tender chicken...  │
│                                                          │
│  ⏱️ Prep: 15 min | Cook: 20 min | Total: 35 min        │
│  👥 Serves: 2-3 people                                  │
│  🌶️ Difficulty: Medium                                 │
│                                                          │
│  Ingredients:                                            │
│  • 500g Chicken breast                                   │
│  • 200g Tomatoes                                        │
│  • 300g Pasta                                           │
│  ...                                                     │
│                                                          │
│  Instructions:                                           │
│  1. [Step Image] Heat oil in pan...                    │
│  2. [Step Image] Add chicken and cook...                │
│  3. [Step Video] Add tomatoes and simmer...             │
│  ...                                                     │
│                                                          │
│  📊 Nutrition Facts:                                    │
│  [View Full Nutrition Label]                            │
│                                                          │
│  [Approve & Use] [Regenerate] [Edit]                   │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 9. IMPLEMENTATION PHASES

### Phase 1: Foundation (Week 1-2)
1. ✅ OpenRouter integration
2. ✅ AI service classes
3. ✅ Credit system
4. ✅ Usage tracking

### Phase 2: Recipe Generation (Week 3-4)
1. ✅ Recipe generation API
2. ✅ Recipe UI
3. ✅ Recipe storage
4. ✅ Recipe approval workflow

### Phase 3: Nutrition Facts (Week 5)
1. ✅ Nutrition calculation
2. ✅ Nutrition label generation
3. ✅ Compliance checking

### Phase 4: Media Generation (Week 6-7)
1. ✅ Image generation
2. ✅ Video generation
3. ✅ Media storage
4. ✅ Media management

### Phase 5: Charging & Billing (Week 8)
1. ✅ Subscription tiers
2. ✅ Credit system
3. ✅ Billing integration
4. ✅ Usage reports

---

## 10. COST ANALYSIS

### 10.1 OpenRouter Costs (Estimated)

**Text Generation (GPT-4 Turbo):**
- Input: $0.01 per 1K tokens
- Output: $0.03 per 1K tokens
- Average recipe: ~1,500 tokens = ~$0.05

**Image Generation (DALL-E 3):**
- Standard: $0.04 per image
- HD: $0.08 per image

**Video Generation (Runway Gen-2):**
- ~$0.05 per second
- 15-second video = $0.75

### 10.2 Platform Pricing Strategy

**Markup:**
- Add 20-50% markup to OpenRouter costs
- Cover platform costs + profit margin

**Example:**
- OpenRouter cost: $0.05 (recipe)
- Platform markup: 20% = $0.01
- Total cost: $0.06
- Charge customer: 5 credits = $0.50 (8x markup for profit)

---

## 11. SUMMARY

### AI Features:
- ✅ Recipe generation
- ✅ Cooking instruction images/videos
- ✅ Nutrition facts calculation
- ✅ OpenRouter integration

### Charging Model:
- ✅ Hybrid: Subscription + Pay-per-use
- ✅ Credit system
- ✅ Three tiers (Starter, Professional, Enterprise)
- ✅ Transparent pricing

### SuperAdmin:
- ✅ AI configuration
- ✅ Model selection
- ✅ Pricing management
- ✅ Usage monitoring

---

**This AI-powered F&B system will help companies create professional recipes, cooking instructions, and nutrition facts automatically!**

