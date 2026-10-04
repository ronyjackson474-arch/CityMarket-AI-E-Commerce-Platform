# Task: Phase 3 — AI Intelligence

## Objective
Add AI-powered features: customer support chatbot, recommendation engine, admin assistant for sales insights, advanced campaign management.

## Files to Create/Modify
- New services: AiChatService, RecommendationService, AiAdminAssistantService
- New/updated controllers: ChatController (API + web), RecommendationController, AdminAiController
- New migrations: chat_conversations, chat_messages, campaigns (if needed)
- New views: storefront/chat (support chatbot UI), admin/ai-assistant
- Tests for AI features (mocked LLM responses)

## Acceptance Criteria

### AI Customer Support Chatbot
- [ ] Chat UI on storefront (/support)
- [ ] Conversational AI that can: track orders, answer FAQs, assist with returns
- [ ] Maintains conversation context across messages
- [ ] API: GET /api/chat/conversations, POST /api/chat/send

### AI Recommendation Engine
- [ ] Recommends products based on user activity (views, searches, purchases)
- [ ] "Recommended for You" section on home page for logged-in users
- [ ] API: GET /api/recommendations

### AI Admin Assistant
- [ ] Admin can ask questions in natural language
- [ ] "Show today's sales", "Best selling products", "Low stock alerts"
- [ ] Returns structured data insights
- [ ] API: POST /api/admin/ai/query (admin only)

### Advanced Campaigns
- [ ] Flash sale campaigns with countdown timers
- [ ] Campaign-specific pricing
- [ ] Admin can create/activate/deactivate campaigns
