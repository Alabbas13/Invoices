<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\invoices;
use App\Models\invoices_attachments;
use App\Models\invoices_details;
use App\Models\products;
use App\Models\sections;
use App\Models\User;
use App\Notifications\AddInvoice;
use App\Notifications\Add_Invoice_New;;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = invoices::all();
        return view('invoices.invoices', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = sections::all();
        return view('invoices.add_invoices', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'invoice_number' => 'required|unique:invoices|max:255',
            'invoice_Date' => 'required',
            'Due_date' => 'required',
            'product' => 'required',
            'section' => 'required',
            'Amount_collection' => 'required',
            'Amount_Commission' => 'required',
            'Discount' => 'required',
            'Value_VAT' => 'required',
            'Rate_VAT' => 'required',
            'note' => 'required',
        ], [
            'invoice_number.required' => 'يرجى إدخال رقم الفاتورة',
            'invoice_number.unique' => 'رقم الفاتورة هذا مستخدم من قبل',
            'invoice_Date.required' => 'يرجى إدخال تاريخ الفاتورة',
            'Due_date.required' => 'يرجى إدخال تاريخ الاستحقاق',
            'section.required' => 'يرجى إدخال اسم القسم',
            'Amount_collection.required' => 'يرجى إدخال مبلغ التحصيل',
            'Amount_Commission.required' => 'يرجى إدخال مبلغ العمولة',
            'Discount.required' => 'يرجى إدخال قيمة الخصم',
            'Value_VAT.required' => 'يرجى تحديد نسبة الضريبة',
            'note.required' => 'required'
        ]);
        invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
        ]);
  
        $invoice_id = invoices::latest()->first()->id;
        invoices_details::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => (Auth::user()->name),
        ]);

        if ($request->hasFile('pic')) {

            $invoice_id = Invoices::latest()->first()->id;
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoice_number = $request->invoice_number;

            $attachments = new invoices_attachments();
            $attachments->file_name = $file_name;
            $attachments->invoice_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

            // move pic الصورة تحفظ على السيرفر اما بالقاعدة يحفظ بس الاسم
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);
        }

        //$user = User::first();
        //Notification::send($user, new AddInvoice($invoice_id));

        $user = User::find(Auth::user()->id);
        $invoices = Invoices::latest()->first();
        //$user->notify(new \App\Notifications\Add_Invoice_New($invoices));
        Notification::send($user, new Add_Invoice_New($invoices));

        session()->flash('Add', 'تم اضافة الفاتورة بنجاح');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.status_update', compact('invoices'));
    }
    public function Status_update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'Status' => 'required',
            'Payment_Date' => 'required',

        ], [
            'Status.required' => 'يرجى تحديد حالة الدفع',
            'Payment_Date.required' => 'يرجى تحديد تاريخ الدفع'
        ]);
        $invoices = invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            invoices_Details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        } else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            invoices_Details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $invoices = invoices::where("id", $id)->first();
        $sections = sections::all();
        return view('invoices.edit_invoices', compact('invoices', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $invoices = invoices::findOrFail($request->invoice_id);
        $invoices->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
        ]);

        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //في حال حذف الفاتورة من قاعدة البيانات بشكل نهائي رح يحذف المرفقات أما في حال حذف بعملية سوف ديليت ما رح يحذ المرفقات لانو ممكن يحتاجن
        $id = $request->invoice_id;
        $invoices = invoices::where('id', $id)->first();
        $Details = invoices_attachments::where('invoice_id', $id)->first();

        $id_page = $request->id_page;


        if (!$id_page == 2) {

            if (!empty($Details->invoice_number)) {

                Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number);
            }

            $invoices->forceDelete();
            session()->flash('delete_invoice');
            return redirect('/invoices');
        } else {
            $invoices->delete();
            session()->flash('archive_invoice');
            return redirect('/InvoiceAchive');
        }
    }

    public function getproducts($id)
    {
        $products = DB::table("products")->where("section_id", $id)->pluck("product_name", "id");
        return json_encode($products);
    }
    public function paid_invoices()
    {
        $invoices = invoices::where('Value_Status', 1)->get();
        return view('invoices.paid_invoices', compact('invoices'));
    }
    public function unpaid_invoices()
    {
        $invoices = invoices::where('Value_Status', 2)->get();
        return view('invoices.unpaid_invoices', compact('invoices'));
    }
    public function partial_invoices()
    {
        $invoices = invoices::where('Value_Status', 3)->get();
        return view('invoices.partial_invoices', compact('invoices'));
    }
    public function print_invoice($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.print_invoice', compact('invoices'));
    }
    public function export()
    {
        return Excel::download(new InvoicesExport, 'invoices.xlsx');
    }
    public function markAll (Request $request)
    {
        $userUnreadNotification = auth()->user()->unreadNotifications;
        if($userUnreadNotification)
        {
            $userUnreadNotification->markAsRead();
            return back();
        }
    }
}
