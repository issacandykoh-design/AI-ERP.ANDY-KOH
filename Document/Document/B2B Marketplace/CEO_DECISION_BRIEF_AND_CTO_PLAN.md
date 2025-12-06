# B2B Marketplace & AI Credits — CEO Decision Brief

## Executive Summary

Launch a B2B marketplace with account-required purchases, multi-merchant orders, Singapore delivery integrations, customizable pricing, platform fees, and a unified billing model that includes module add-ons and AI credits. The decision requires confirming core business policies that drive revenue, adoption, margins, and operational complexity.

## Decisions Required (Business Logic)

1. Target segments: Which industries and company sizes (SMB, mid-market, enterprise) are priority?
2. Marketplace visibility: Should module listings and tier pricing be public or gated behind login?
3. Primary billing unit: Confirm packages as primary with module add-ons and unified invoices.
4. Pricing precedence: Final order among base price → public price → tier pricing → client-specific pricing → volume discounts.
5. Tier visibility: Publish tier pricing to drive signups or keep private for negotiated deals?
6. Client-specific pricing: Allow merchants to assign bespoke pricing per client at scale; set governance.
7. Transaction fees: Approve fee levels and rules by order type (public vs B2B; tiered by order value).
8. Merchant subscriptions: Confirm tiers, feature gates, and price points (Basic/Premium/Enterprise).
9. Bundle discounts: Offer bundle pricing for multi-module add-ons; define eligibility and limits.
10. AI credits strategy: Include credits in packages, allow standalone purchases, or both; define reset/expiry.
11. Promotions: Approve “buy $X get $Y bonus credits” structure; caps, expiry, eligibility, and audit.
12. Delivery partners: Prioritize initial Singapore partners (e.g., Ninja Van, Lalamove, Grab Express) and delivery options (standard/express/same-day/scheduled).
13. Shipping pricing: Adopt zone + weight/distance rules; set free-shipping thresholds per merchant.
14. Multi-merchant orders: Approve single checkout with merchant-level splits, shipments, and tracking.
15. Operational fallback: Define manual delivery fallback policy when APIs fail; who absorbs risk/cost?
16. Rate limits & exhaustion: Per-company AI rate limits; strict blocking when credits run out vs. grace queue.
17. Refunds & cancellations: Policies for packages, module add-ons, credits; proration rules mid-cycle.
18. Compliance: PCI-compliant gateways, webhook integrity, tenant isolation, and audit requirements.

## Monetization & KPIs

- Monetization: Transaction fees, merchant subscription tiers, module add-ons, AI credit sales, bundle discounts.
- KPIs: Marketplace GMV, fee revenue, subscription revenue, credit sales, merchant onboarding, active modules per company, delivery success rate, average shipping cost, credit utilization, churn, add-on attachment rate, credit repurchase frequency.

## Approval Checklist

- Confirm pricing and fee policies (tiers, client-specific, volume, transaction fees).
- Confirm delivery partners and shipping options; approve fallback policy.
- Approve unified billing across packages + add-ons + credits.
- Approve AI credits policies (inclusion, purchases, promotions, rate limits, exhaustion handling).
- Approve refund/cancellation and proration policies.
- Approve compliance and audit standards.

## CTO Action Plan (Phased)

### Phase 1 — Policy Encoding & Data Model
- Codify approved business policies in configuration and policy docs.
- Finalize database schemas for marketplace, pricing, delivery, fees, subscriptions, AI credits, and promotions.
- Enable feature flags for phased rollout and safe fallback.

### Phase 2 — Pricing & Savings
- Implement PricingService precedence and savings computation with comparison display after login.
- Support public vs B2B tier visibility and client-specific assignments via Client module.
- Add volume discounts and bundle discount hooks for module add-ons.

### Phase 3 — Delivery Integrations & Cart
- Implement integration layer and adapters for selected Singapore delivery partners.
- Build shipping cost calculator (zone + weight/distance + option multipliers).
- Implement multi-merchant cart and order splitting with merchant-level shipments and tracking.
- Define and implement operational fallback for API failures.

### Phase 4 — Unified Billing & Fees
- Implement transaction fee calculation and collection per order type.
- Implement merchant subscription tiers with feature gates and product limits.
- Generate unified invoices (package + add-ons + credits) with proration, upgrades/downgrades, refunds.
- Update payment gateways and webhooks for module subscriptions and credit purchases.

### Phase 5 — AI Credits & Usage
- Implement package-included credits, standalone credit purchases, and promotions with caps.
- Add real-time balance checks, deduction per model/content type, and per-company rate limits.
- Implement usage tracking by module and content type; build profile/dashboard and exports.
- Implement credit exhaustion handling (block, notify, purchase prompt, optional grace/queue).

### Phase 6 — Admin, Security, Testing, Rollout
- Build admin panels for marketplace, credit packages/promotions, subscriptions, and analytics.
- Enforce multi-tenant isolation, PCI-compliant payment practices, webhook verification, and audit logging.
- Ship unit/feature/integration/performance tests across pricing, delivery, billing, credits, and analytics.
- Plan migration scripts, staged rollout, monitoring, and rollback.

## Dependencies & Risks

- High-risk areas: payment changes, data migration, delivery API reliability, rate-limit cost control.
- Mitigations: feature flags, staged rollout, comprehensive tests, monitoring, rollback, clear documentation.

---

Prepared for executive decision; implementation can begin upon approval of policies above.
