<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CircularItem;
use App\Support\MemberAccess;
use App\Support\NotifyOutbox;
use App\Support\PortalCache;
use App\Support\PortalContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $memberId = $this->memberId($request);
        $member = $this->member($memberId);

        if (! $member) {
            return response()->json(['message' => 'Member not found.'], 404);
        }

        return PortalCache::noStoreJson([
            'member' => $this->memberPayload($member),
            'summary' => $this->summaryPayload($memberId, $member),
            'services' => $this->services(),
            'circular_highlight' => $this->dashboardHighlight(),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        return PortalCache::noStoreJson($this->summaryPayload($this->memberId($request)));
    }

    private function memberId(Request $request): string
    {
        return trim((string) data_get($request->user(), 'member_id'));
    }

    private function member(string $memberId): ?object
    {
        return MemberAccess::activeMemberQuery()
            ->leftJoin('List_MemExpType as mt', 'c.MemExpTypeID', '=', 'mt.MemExpTypeID')
            ->where('c.PrvCusID', $memberId)
            ->select([
                'c.PrvCusID',
                'c.Title',
                'c.CusName',
                'c.CreditBal',
                'c.CreditAmt',
                'mt.MemExpTypeName',
                'cc.Remarks as MemberCategory',
                DB::raw("(SELECT COALESCE(SUM(COALESCE(cl.DrAmt, 0) - COALESCE(cl.CrAmt, 0)), 0) FROM Customer_ledger cl WHERE cl.PrvCusId = c.PrvCusID AND cl.InvMRN <> '0') as LedgerDue"),
            ])
            ->first();
    }

    private function memberPayload(object $member): array
    {
        $memberId = (string) $member->PrvCusID;
        $name = trim(($member->Title ? $member->Title . ' ' : '') . $member->CusName);
        $words = preg_split('/\s+/', trim((string) $member->CusName)) ?: [];

        return [
            'id' => $memberId,
            'name' => $name !== '' ? $name : 'Member',
            'initials' => collect(array_slice(array_filter($words), 0, 2))
                ->map(fn (string $word) => strtoupper(mb_substr($word, 0, 1)))
                ->join(''),
            'category' => (string) ($member->MemberCategory ?? ''),
            'status' => (string) ($member->MemExpTypeName ?? ''),
            'credit_limit' => (float) ($member->CreditAmt ?? 0),
            'credit_balance' => (float) ($member->CreditBal ?? 0),
            'has_photo' => PortalCache::hasMemberPhoto($memberId),
            'photo_url' => PortalCache::memberPhotoUrl($memberId),
            'photo_thumb_url' => PortalCache::memberPhotoThumbUrl($memberId),
            'photo_preview_url' => PortalCache::memberPhotoPreviewUrl($memberId),
        ];
    }

    private function summaryPayload(string $memberId, ?object $member = null): array
    {
        $member = $member ?: $this->member($memberId) ?: (object) ['CreditAmt' => 0];
        $ledgerDue = property_exists($member, 'LedgerDue') ? $member->LedgerDue : null;

        if ($ledgerDue === null) {
            try {
                $ledger = DB::table('Customer_ledger')
                    ->where('PrvCusId', $memberId)
                    ->where('InvMRN', '<>', '0')
                    ->selectRaw('COALESCE(SUM(COALESCE(DrAmt, 0) - COALESCE(CrAmt, 0)), 0) as Due')
                    ->first();
                $ledgerDue = $ledger->Due ?? 0;
            } catch (\Throwable $e) {
                Log::warning('Unable to load API dashboard ledger totals.', [
                    'member_id' => $memberId,
                    'error' => $e->getMessage(),
                ]);

                $ledgerDue = 0;
            }
        }

        $totalDue = max(0, (float) $ledgerDue);
        $creditLimit = (float) ($member->CreditAmt ?? 0);
        NotifyOutbox::dueReminder($memberId, $totalDue, $creditLimit);

        return [
            'creditBal' => $creditLimit - $totalDue,
            'totalDue' => $totalDue,
            'creditLimit' => $creditLimit,
        ];
    }

    private function dashboardHighlight(): ?array
    {
        return PortalCache::rememberResilient(
            PortalContent::DASHBOARD_CIRCULAR_HIGHLIGHT_CACHE_KEY,
            PortalContent::DASHBOARD_CIRCULAR_HIGHLIGHT_STALE_CACHE_KEY,
            now()->addMinutes(5),
            now()->addDay(),
            function (): ?array {
                $circular = CircularItem::query()
                    ->visible()
                    ->orderByDesc('dtt_ad_start')
                    ->orderByDesc('id_career_key')
                    ->first();

                if (! $circular) {
                    return null;
                }

                return [
                    'title' => trim((string) ($circular->tx_title ?: 'Circular')),
                    'excerpt' => $circular->excerpt,
                    'image_url' => $circular->display_image_url,
                    'image_thumb_url' => $circular->display_image_thumb_url,
                    'source_url' => $circular->action_url,
                    'start_date' => $circular->start_date_label,
                    'close_date' => $circular->has_distinct_close_date ? $circular->close_date_label : null,
                    'badge_month' => $circular->dtt_ad_start?->format('M') ?? 'NOW',
                    'badge_day' => $circular->dtt_ad_start?->format('d') ?? '--',
                ];
            },
            null
        );
    }

    private function services(): array
    {
        return [
            ['key' => 'directory', 'icon' => 'group', 'label' => 'Member Directory'],
            ['key' => 'executive', 'icon' => 'gavel', 'label' => 'General Committee'],
            ['key' => 'ledger', 'icon' => 'account_balance_wallet', 'label' => 'Ledger'],
            ['key' => 'circulars', 'icon' => 'article', 'label' => 'Circular'],
            ['key' => 'employee-directory', 'icon' => 'badge', 'label' => 'Employee Directory'],
            ['key' => 'contact', 'icon' => 'call', 'label' => 'Contact'],
        ];
    }
}
