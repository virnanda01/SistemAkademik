<?php

namespace App\Http\Controllers\Admin;

use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KrsRequest;

class KhsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data_tahun_akademik = DB::select("
                            SELECT id,semester,CONCAT(tahun_akademik,'/') AS th_akademik
                            FROM tahun_akademik
                        ");
        return view('admin.khs.index', compact('data_tahun_akademik'));
    }

    public function find(Request $request)
    {
       $request->validate([
           'nim' => 'required',
           'tahun_akademik_id' => 'required'
       ]);

       $mhs = Mahasiswa::where('nim', $request->nim)->first();
       if(is_null($mhs)) {
            return redirect()->back()->with([
                'message' => 'mahasiswa belum terdaftar !',
                'alert-type' => 'info'
            ]);
       }

       $selectKhs = DB::select("  SELECT 
                                krs.tahun_akademik_id,
                                krs.mata_kuliah_id,
                                mata_kuliah.nama_mata_kuliah,
                                mata_kuliah.kode_mata_kuliah,
                                mata_kuliah.sks,
                                krs.nilai
                            FROM krs 
                            JOIN mata_kuliah
                            ON (krs.mata_kuliah_id = mata_kuliah.id)
                            WHERE krs.nim = $request->nim 
                            AND krs.tahun_akademik_id = $request->tahun_akademik_id
                        ");
        if(count($selectKhs) == 0) {
            return redirect()->back()->with([
                'message' => 'mahasiswa belum terdaftar pada tahun yang dipilih !',
                'alert-type' => 'info'
            ]);
       }

       $data_khs = [
           'mhs_data' => $selectKhs,
           'nim' => $request->nim,
           'nama_lengkap' => $mhs->nama_lengkap,
           'prody' => $mhs->program_study->nama_prody,
           'tahun_akademik' => TahunAkademik::where('id', $request->tahun_akademik_id)->first()->tahun_akademik,
           'semester' => TahunAkademik::where('id', $request->tahun_akademik_id)->first()->semester
       ];

        return view('admin.khs.show', compact('data_khs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($nim, $tahun_akademik_id)
    {
        $data_mata_kuliah = MataKuliah::get(['nama_mata_kuliah','id']);
        $tahun_akademik = TahunAkademik::find($tahun_akademik_id);

        return view('admin.krs.create', compact('nim','tahun_akademik', 'data_mata_kuliah'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KrsRequest $request)
    {
        Krs::create($request->validated() + ['nilai' => 0]);

        return redirect()->route('admin.krs.index')->with([
            'message' => 'berhasi di buat !',
            'alert-type' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Krs $krs)
    {
        $data_mata_kuliah = MataKuliah::get(['nama_mata_kuliah','id']);

        return view('admin.krs.edit', compact('krs', 'data_mata_kuliah'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KrsRequest $request, Krs $krs)
    {
        $krs->update($request->validated() + ['nilai' => 0]);

        return redirect()->route('admin.krs.index')->with([
            'message' => 'berhasil di ganti !',
            'alert-type' => 'info'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Krs $krs)
    {
        $krs->delete();

        return redirect()->back()->with([
            'message' => 'berhasi di hapus !',
            'alert-type' => 'danger'
        ]);
    }
}
