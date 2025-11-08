<?php

// use App\Models\Member;
// use App\Models\Invoice;
// use Illuminate\Support\Carbon;

// if (!function_exists('generateMemberNumber')) {
//     function generateMemberNumber(): string
//     {
//         $timestamp = Carbon::parse(REQUEST_TIMESTAMP);
        
//         // Use max ID directly for sequential incrementation
//         $sequence = Member::max('id') + 1;
        
//         do {
//             // Format member number
//             $number = sprintf(
//                 "MWAK-%d-%s-%s",
//                 $sequence,               // Sequential number
//                 $timestamp->format('Y'), // Year
//                 $timestamp->format('m')  // Month
//             );

//             // Only proceed to the next sequence if this _uid already exists
//             if (Member::where('_uid', $number)->exists()) {
//                 $sequence++;
//             } else {
//                 break; // Exit loop if unique number is found
//             }
//         } while (true);

//         return $number;
//     }
// }

// if (!function_exists('generateInvoiceNumber')) {
//     function generateInvoiceNumber(string $clientCode): string
//     {
//         $timestamp = Carbon::parse(REQUEST_TIMESTAMP);

//         // Start with the next available ID as the sequence number
//         $sequence = Invoice::max('id') + 1;

//         do {
//             // Format invoice number
//             $number = sprintf(
//                 "INV-%d-%s-%s",
//                 $sequence,                 // Sequential number
//                 $timestamp->format('Ymd'), // Date element in YYYYMMDD format
//                 $clientCode                // Client or account code
//             );

//             // Check for uniqueness and increment sequence only if duplicate exists
//             if (Invoice::where('_uid', $number)->exists()) {
//                 $sequence++;
//             } else {
//                 break; // Unique number found, exit loop
//             }
//         } while (true);

//         return $number;
//     }
// }
