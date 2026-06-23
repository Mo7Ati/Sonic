---
name: notifications-feature
description: How order/custom notifications work across the Laravel backend and the Expo mobile app
metadata:
  type: project
---

Push + in-app notifications were built on 2026-06-17 spanning the Laravel backend (`C:\Users\Dawly\projects\Laravel\Sonic`) and the Expo app (`C:\Users\Dawly\projects\react-native\sonic-mobile`).

**Architecture (decided with the user):**
- Delivery: Laravel `Notification` classes send via BOTH `database` (in-app bell list) and the Expo push channel (`laravel-notification-channels/expo`). Mobile uses **Expo Push** (`expo-notifications`), not raw FCM/APNs.
- Order status: `App\Observers\OrderObserver` fires `OrderStatusChanged` on `wasChanged('status')`. Notifications are `ShouldQueue` — **a queue worker must run** (sync in tests).
- Custom broadcasts: Admin Filament page `App\Filament\Pages\SendNotification` → `CustomNotification`.
- **Push only** for now; live foreground updates over Reverb were intentionally deferred (can be added later without reworking the notification layer).

**Testing / credentials:**
- **iOS + Expo Go push works with zero credentials** — Expo Go only dropped *Android* push in SDK 53; iOS still delivers via Expo Go's own APNs key. Confirmed working end-to-end this way. The token is bound to Expo Go, so a standalone/dev/TestFlight build still needs the project's own APNs key (Apple Developer account).
- **Android** has no Expo Go push — needs a dev build + Firebase/FCM credentials in EAS.
- iOS push for a real (non-Expo-Go) build deferred until an Apple Developer account exists.
- Backend tests run on sqlite `:memory:` with `DB_FOREIGN_KEYS=false` (phpunit.xml). The pre-existing `StoreCategoryHierarchyTest` is broken independent of this work (asserts on a nonexistent `slug`/`roots()`).

See [[autonomous-execution-preference]].
