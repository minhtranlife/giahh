<?php

namespace App\Http\Controllers;

use App\DmHangHoa;
use App\DmLoaiGia;
use App\DmLoaiHh;
use App\DmThiTruong;
use App\DmThoiDiem;
use App\GiaHangHoa;
use App\GiaHangHoaDefault;
use App\HsGiaHangHoa;
use App\PNhomHangHoa;
use App\TtPhongBan;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class HsGiaHangHoaDP_LaoCaiController extends Controller
{
    public function thoidiem()
    {
        if(Session::has('admin')){
            $model = PNhomHangHoa::where('manhom','02')
                ->get();
            return view('manage.giahhdv.hhdp_laocai.thoidiem.index')
                ->with('model',$model)
                ->with('pageTitle','Chọn thời điểm nhập báo cáo giá hàng hóa do địa phương quy định');
        }else{
            return view('errors.notlogin');
        }
    }

    public function showthoidiem()
    {
        if(Session::has('admin')){
            $model = PNhomHangHoa::where('manhom','02')
                ->get();
            return view('manage.giahhdv.hhdp_laocai.thoidiem.showindex')
                ->with('model',$model)
                ->with('pageTitle','Chọn thời điểm xem báo cáo giá hàng hóa do địa phương quy định');
        }else
            return view('errors.notlogin');
    }

    public function index($masopnhom,$nam)
    {
        if(Session::has('admin')){

            $model = HsGiaHangHoa::where('masopnhom',$masopnhom)
                ->where('nam',$nam)
                ->where('phanloai','DP')
                ->where('mahuyen',session('admin')->mahuyen)
                ->get();

            $modelpb = TtPhongBan::all();
            $this->getDetail($model);

            return view('manage.giahhdv.hhdp_laocai.index')
                ->with('model',$model)
                ->with('modelpb',$modelpb)
                ->with('masopnhom',$masopnhom)
                ->with('nam',$nam)
                ->with('url','/giahhdv-diaphuong/')
                ->with('pageTitle','Thông tin hồ sơ giá hàng hóa do địa phương quy định');
        }else
            return view('errors.notlogin');
    }

    public function showindex($masopnhom,$nam,$pb)
    {
        if(Session::has('admin')){
            $model = HsGiaHangHoa::where('masopnhom',$masopnhom)
                ->where('nam',$nam)
                ->where('phanloai','DP')
                ->where('trangthai','Hoàn tất')
                ->get();
            if($pb != 'all') {
                $model = $model->where('mahuyen', $pb);
            }
            $modelpb = TtPhongBan::all();
            $this->getDetail($model);

            return view('manage.giahhdv.hhdp_laocai.showindex')
                ->with('model',$model)
                ->with('modelpb',$modelpb)
                ->with('masopnhom',$masopnhom)
                ->with('nam',$nam)
                ->with('pb',$pb)
                ->with('url','/thongtin-diaphuong/')
                ->with('pageTitle','Thông tin hồ sơ giá hàng hóa do địa phương quy định');
        }else
            return view('errors.notlogin');
    }

    function getDetail($model)
    {
        $dmpb = array_column(TtPhongBan::select('ma', 'ten')->get()->toarray(), 'ten', 'ma');
        $dmloaigia = array_column(DmLoaiGia::select('maloaigia', 'tenloaigia')->get()->toarray(), 'tenloaigia', 'maloaigia');
        $dmloaihanghoa = array_column(DmLoaiHh::select('maloaihh', 'tenloaihh')->get()->toarray(), 'tenloaihh', 'maloaihh');
        foreach ($model as $ct) {
            $ct->tenpb = array_key_exists($ct->mahuyen, $dmpb) ? $dmpb[$ct->mahuyen] : '';
            $ct->tenloaigia = array_key_exists($ct->maloaigia, $dmloaigia) ? $dmloaigia[$ct->maloaigia] : '';
            $ct->tenloaihh = array_key_exists($ct->maloaihh, $dmloaihanghoa) ? $dmloaihanghoa[$ct->maloaihh] : '';
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($masopnhom)
    {
        if(Session::has('admin')){
            $mahuyen=session('admin')->mahuyen;
            GiaHangHoaDefault::where('mahuyen',$mahuyen)->delete();

            $loaigia = DmLoaiGia::where('pl','Hàng hóa, dịch vụ')->get();
            $loaihh = DmLoaiHh::all();

            $thitruong= DmThiTruong::all();
            $nhomhh = PNhomHangHoa::select('masopnhom','tenpnhom')->where('theodoi','Có')->get();
            $hanghoa = DmHangHoa::select('masopnhom','mahh',DB::raw($mahuyen." as 'mahuyen'"),DB::raw("1 as 'soluong'"),DB::raw("0 as 'giatu'"),DB::raw("0 as 'giaden'"))->where('masopnhom',$masopnhom)->get()->toarray();
            GiaHangHoaDefault::insert($hanghoa);

            $dmhh = array_column(DmHangHoa::select('mahh','tenhh')->where('masopnhom',$masopnhom)->get()->toarray(),'tenhh','mahh');
            $model=GiaHangHoaDefault::where('mahuyen',$mahuyen)->get();
            foreach($model as $ct){
                $ct->tenhh=$dmhh[$ct->mahh];
            }
            $dmhanghoa = DmHangHoa::where('masopnhom',$masopnhom)->get();

            return view('manage.giahhdv.hhdp_laocai.create')
                ->with('model',$model)
                ->with('masopnhom',$masopnhom)
                ->with('loaigia',$loaigia)
                ->with('loaihh',$loaihh)
                ->with('nhomhh',$nhomhh)
                ->with('dmhanghoa',$dmhanghoa)
                ->with('thitruong',$thitruong)
                ->with('pageTitle','Thông tin giá hàng hóa do địa phương quy định thêm mới');
        }else
            return view('errors.notlogin');
    }

    public function create_dk($masopnhom)
    {
        if(Session::has('admin')){
            $loaigia = DmLoaiGia::where('pl','Hàng hóa, dịch vụ')->get();
            $loaihh = DmLoaiHh::all();
            $thitruong= DmThiTruong::all();
            return view('manage.giahhdv.hhdp_laocai.create_dk')
                ->with('masopnhom',$masopnhom)
                ->with('loaigia',$loaigia)
                ->with('loaihh',$loaihh)
                ->with('thitruong',$thitruong)
                ->with('pageTitle','Thông tin giá hàng hóa do địa phương quy định thêm mới');
        }else
            return view('errors.notlogin');
    }

    public function store(Request $request)
    {
        if(Session::has('admin')){
            $insert = $request->all();
            $date = date_create(getDateToDb($insert['tgnhap']));
            $thang = date_format($date,'m');
            $mahs = getdate()[0];
            $mahuyen = session('admin')->mahuyen;

            $model = new HsGiaHangHoa();
            $model->tgnhap = getDateToDb($insert['tgnhap']);
            $model->thitruong = $insert['thitruong'];
            $model->maloaihh = $insert['maloaihh'];
            $model->maloaigia = $insert['maloaigia'];
            $model->phanloai = 'DP';
            $model->hoso = 'CHITIET';
            $model->quy = Thang2Quy($thang);
            $model->thang = date_format($date,'m');
            $model->nam = date_format($date,'Y');
            $model->mahuyen = $mahuyen;
            $model->mahs = $mahs;
            $model->masopnhom = $insert['masopnhom'];
            if($model->save()){
                $hanghoa = GiaHangHoaDefault::select('masopnhom','mahh','giatu','giaden','soluong','nguontin',DB::raw($mahs." as mahs"))->where('mahuyen',$mahuyen)->get()->toarray();
                GiaHangHoa::insert($hanghoa);
            }

            return redirect('giahhdv-diaphuong/maso='.$insert['masopnhom'].'/nam='.date_format($date,'Y'));

        }else
            return view('errors.notlogin');
    }

    public function store_dk(Request $request)
    {
        if(Session::has('admin')){
            $insert = $request->all();
            $date = date_create(getDateToDb($insert['tgnhap']));
            $thang = date_format($date,'m');
            $mahs = getdate()[0];
            $mahuyen = session('admin')->mahuyen;

            $model = new HsGiaHangHoa();

            $file=$request->file('filedk');
            if(isset($file)){
                $filename = $mahs.'_1_'.chuanhoatruong($file->getClientOriginalName());
                $file->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk = $filename;
            }

            $file1=$request->file('filedk1');
            if(isset($file1)){
                $filename = $mahs.'_2_'.chuanhoatruong($file1->getClientOriginalName());
                $file1->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk1 = $filename;
            }

            $file2=$request->file('filedk2');
            if(isset($file2)){
                $filename = $mahs.'_3_'.chuanhoatruong($file2->getClientOriginalName());
                $file2->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk2 = $filename;
            }

            $file3=$request->file('filedk3');
            if(isset($file3)){
                $filename = $mahs.'_4_'.chuanhoatruong($file3->getClientOriginalName());
                $file3->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk3 = $filename;
            }

            $file4=$request->file('filedk4');
            if(isset($file4)){
                $filename = $mahs.'_5_'.chuanhoatruong($file4->getClientOriginalName());
                $file4->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk4 = $filename;
            }

            $model->tgnhap = getDateToDb($insert['tgnhap']);
            $model->thitruong = $insert['thitruong'];
            $model->maloaihh = $insert['maloaihh'];
            $model->maloaigia = $insert['maloaigia'];
            $model->hoso = 'DINHKEM';
            $model->phanloai = 'DP';
            $model->quy = Thang2Quy($thang);
            $model->thang = date_format($date,'m');
            $model->nam = date_format($date,'Y');
            $model->mahuyen = $mahuyen;
            $model->mahs = $mahs;
            $model->masopnhom = $insert['masopnhom'];
            $model->save();

            return redirect('giahhdv-diaphuong/maso='.$insert['masopnhom'].'/nam='.date_format($date,'Y'));

        }else
            return view('errors.notlogin');
    }

    public function show($id)
    {
        if(Session::has('admin')){
            $model = HsGiaHangHoa::findOrFail($id);
            $modeltthh = GiaHangHoa::where('mahs',$model->mahs)->get();
            $loaigia = DmLoaiGia::where('pl','Hàng hóa, dịch vụ');
            $loaihh = DmLoaiHh::all();
            $thitruong= DmThiTruong::all();
            //dd($thitruong);
            $dmhanghoa = array_column(DmHangHoa::select('mahh','tenhh')->get()->toarray(),'tenhh','mahh');
            foreach($modeltthh as $ct){
                $ct->tenhh=$dmhanghoa[$ct->mahh];
            }
            return view('manage.giahhdv.hhdp_laocai.show')
                ->with('model',$model)
                ->with('modeltthh',$modeltthh)
                ->with('loaigia',$loaigia)
                ->with('loaihh',$loaihh)
                ->with('thitruong',$thitruong)
                ->with('pageTitle','Thông tin giá hàng hóa, dịch vụ chi tiết');
        }else
            return view('errors.notlogin');
    }

    public function view($id)
    {
        if(Session::has('admin')){
            $model = HsGiaHangHoa::findOrFail($id);
            $modeltthh = GiaHangHoa::where('mahs',$model->mahs)->get();
            $loaigia = DmLoaiGia::where('pl','Hàng hóa, dịch vụ')->get();
            $loaihh = DmLoaiHh::all();
            $thitruong= DmThiTruong::all();
            //dd($loaigia);
            $dmhanghoa = array_column(DmHangHoa::select('mahh','tenhh')->get()->toarray(),'tenhh','mahh');
            foreach($modeltthh as $ct){
                $ct->tenhh=$dmhanghoa[$ct->mahh];
            }
            return view('manage.giahhdv.hhdp_laocai.view')
                ->with('model',$model)
                ->with('modeltthh',$modeltthh)
                ->with('loaigia',$loaigia)
                ->with('loaihh',$loaihh)
                ->with('thitruong',$thitruong)
                ->with('pageTitle','Thông tin giá hàng hóa, dịch vụ chi tiết');
        }else
            return view('errors.notlogin');
    }

    public function edit($id)
    {
        if(Session::has('admin')){
            $model = HsGiaHangHoa::findOrFail($id);
            $masopnhom = $model->masopnhom;
            $modeltthh = GiaHangHoa::where('mahs',$model->mahs)->get();

            $dmhh = array_column(DmHangHoa::select('mahh','tenhh')->get()->toarray(),'tenhh','mahh');
            foreach($modeltthh as $ct){
                $ct->tenhh=$dmhh[$ct->mahh];
            }

            //dd($modeltthh);
            $loaigia = DmLoaiGia::where('pl','Hàng hóa, dịch vụ')->get();
            $loaihh = DmLoaiHh::all();
            $thitruong= DmThiTruong::all();
            $nhomhh = PNhomHangHoa::where('theodoi','Có')->get();
            $dmhanghoa = DmHangHoa::where('masopnhom',$model->masopnhom)->get();
            return view('manage.giahhdv.hhdp_laocai.edit')
                ->with('model',$model)
                ->with('masopnhom',$masopnhom)
                ->with('modeltthh',$modeltthh)
                ->with('loaigia',$loaigia)
                ->with('loaihh',$loaihh)
                ->with('nhomhh',$nhomhh)
                ->with('thitruong',$thitruong)
                ->with('dmhanghoa',$dmhanghoa)
                ->with('pageTitle','Thông tin giá hàng hóa, dịch vụ chi tiết');
        }else
            return view('errors.notlogin');
    }

    public function edit_dk($id)
    {
        if(Session::has('admin')){
            $model = HsGiaHangHoa::findOrFail($id);
            $loaigia = DmLoaiGia::where('pl','Hàng hóa, dịch vụ')->get();
            $loaihh = DmLoaiHh::all();
            $thitruong= DmThiTruong::all();

            return view('manage.giahhdv.hhdp_laocai.edit_dk')
                ->with('model',$model)
                ->with('loaigia',$loaigia)
                ->with('loaihh',$loaihh)
                ->with('thitruong',$thitruong)
                ->with('pageTitle','Thông tin giá hàng hóa, dịch vụ chi tiết');
        }else
            return view('errors.notlogin');
    }

    public function update(Request $request, $id)
    {
        if(Session::has('admin')){
            $insert = $request->all();
            $date = date_create(getDateToDb($insert['tgnhap']));
            $thang = date_format($date,'m');

            $model = HsGiaHangHoa::findOrFail($id);
            $model->tgnhap = getDateToDb($insert['tgnhap']);
            $model->thitruong = $insert['thitruong'];
            $model->maloaihh = $insert['maloaihh'];
            $model->maloaigia = $insert['maloaigia'];
            $model->quy=Thang2Quy($thang);
            $model->thang = date_format($date,'m');
            $model->nam = date_format($date,'Y');
            $model->save();

            return redirect('giahhdv-diaphuong/maso='.$model->masopnhom.'/nam='.date_format($date,'Y'));

        }else
            return view('errors.notlogin');
    }

    public function update_dk(Request $request, $id)
    {
        if(Session::has('admin')){
            $insert = $request->all();
            $date = date_create(getDateToDb($insert['tgnhap']));
            $thang = date_format($date,'m');

            $model = HsGiaHangHoa::findOrFail($id);
            if(isset($request->filedk)){
                if(file_exists(public_path() . '/data/uploads/attack/'.$model->filedk)){
                    File::Delete(public_path() . '/data/uploads/attack/'.$model->filedk);
                }
                $file=$request->file('filedk');
                $filename = $insert['mahs'].'_1_'.chuanhoatruong($file->getClientOriginalName());
                $file->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk=$filename;
            }

            if(isset($request->filedk1)){
                if(file_exists(public_path() . '/data/uploads/attack/'.$model->filedk1)){
                    File::Delete(public_path() . '/data/uploads/attack/'.$model->filedk1);
                }
                $file=$request->file('filedk1');
                $filename = $insert['mahs'].'_2_'.chuanhoatruong($file->getClientOriginalName());
                $file->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk1=$filename;
            }

            if(isset($request->filedk2)){
                if(file_exists(public_path() . '/data/uploads/attack/'.$model->filedk2)){
                    File::Delete(public_path() . '/data/uploads/attack/'.$model->filedk2);
                }
                $file=$request->file('filedk2');
                $filename = $insert['mahs'].'_3_'.chuanhoatruong($file->getClientOriginalName());
                $file->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk2=$filename;
            }

            if(isset($request->filedk3)){
                if(file_exists(public_path() . '/data/uploads/attack/'.$model->filedk3)){
                    File::Delete(public_path() . '/data/uploads/attack/'.$model->filedk3);
                }
                $file=$request->file('filedk3');
                $filename = $insert['mahs'].'_4_'.chuanhoatruong($file->getClientOriginalName());
                $file->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk3=$filename;
            }

            if(isset($request->filedk4)){
                if(file_exists(public_path() . '/data/uploads/attack/'.$model->filedk4)){
                    File::Delete(public_path() . '/data/uploads/attack/'.$model->filedk4);
                }
                $file=$request->file('filedk4');
                $filename = $insert['mahs'].'_5_'.chuanhoatruong($file->getClientOriginalName());
                $file->move(public_path() . '/data/uploads/attack/', $filename);
                $model->filedk4=$filename;
            }

            $model->tgnhap = getDateToDb($insert['tgnhap']);
            $model->thitruong = $insert['thitruong'];
            $model->maloaihh = $insert['maloaihh'];
            $model->maloaigia = $insert['maloaigia'];
            $model->quy=Thang2Quy($thang);
            $model->thang = date_format($date,'m');
            $model->nam = date_format($date,'Y');
            $model->save();

            return redirect('giahhdv-diaphuong/maso='.$model->masopnhom.'/nam='.date_format($date,'Y'));

        }else
            return view('errors.notlogin');
    }

    public function destroy(Request $request)
    {
        if(Session::has('admin')){
            $input = $request->all();
            $model = HsGiaHangHoa::where('id',$input['iddelete'])->first();
            //dd($model);
            if($model->delete()) {
                GiaHangHoa::where('mahs', $model->mahs)->delete();
            }
            return redirect('giahhdv-diaphuong/maso='.$model->masopnhom.'/nam='.$model->nam);
        }else
            return view('errors.notlogin');
    }

    public function approve(Request $request){
        if(Session::has('admin')){
            $model = HsGiaHangHoa::where('id',$request['idhoantat'])->first();
            //dd($model);
            $model->trangthai = 'Hoàn tất';
            $model->save();
            /*Lịch sử
            if($model->save()){
                $modelh = new ThamDinhGiaH();
                $modelh->mahs = $model->mahs;
                $modelh->thaotac = 'Hoàn tất hồ sơ';
                $modelh->name = session('admin')->name;
                $modelh->username = session('admin')->username;
                $modelh->save();
            }
            */
            return redirect('giahhdv-diaphuong/maso='.$model->masopnhom.'/nam='.$model->nam);
        }else
            return view('errors.notlogin');
    }

    public function unapprove(Request $request){
        if(Session::has('admin')){
            $model = HsGiaHangHoa::where('id',$request['idhuy'])->first();
            //dd($model);
            $model->trangthai = 'Chưa hoàn tất';
            $model->save();
            /*Lịch sử
            if($model->save()){
                $modelh = new ThamDinhGiaH();
                $modelh->mahs = $model->mahs;
                $modelh->thaotac = 'Hoàn tất hồ sơ';
                $modelh->name = session('admin')->name;
                $modelh->username = session('admin')->username;
                $modelh->save();
            }
            */
            return redirect('giahhdv-diaphuong/maso='.$model->masopnhom.'/nam='.$model->nam.'&pb=all');
        }else
            return view('errors.notlogin');
    }

    public function search(){
        if(Session::has('admin')){
            $modelmaloaigia = DmLoaiGia::where('pl','Hàng hóa, dịch vụ')->get();
            $modelmaloaihh = DmLoaiHh::all();
            $modelthitruong = DmThiTruong::all();

            $modelhh = DmHangHoa::where('theodoi','Có')->get();
            return view('manage.giahhdv.hhdp_laocai.search.create')
                ->with('modelmaloaigia',$modelmaloaigia)
                ->with('modelmaloaihh',$modelmaloaihh)
                ->with('modelthitruong',$modelthitruong)
                ->with('modelhh',$modelhh)
                ->with('pageTitle','Tìm kiếm thông tin giá hàng hóa thị trường');
        }else
            return view('errors.notlogin');
    }

    public function viewsearch(Request $request){
        if(Session::has('admin')){

            $_sql="select hsgiahanghoa.*,
                          giahanghoa.mahh,giahanghoa.masopnhom,giahanghoa.giatu,giahanghoa.giaden,giahanghoa.soluong,giahanghoa.nguontin
                                        from hsgiahanghoa, giahanghoa
                                        Where hsgiahanghoa.mahs=giahanghoa.mahs";
            $input=$request->all();

            //Thời gian nhập
            //Từ
            if($input['tgnhaptu']!=null){
                $_sql=$_sql." and hsgiahanghoa.tgnhap >='".date('Y-m-d',strtotime($input['tgnhaptu']))."'";
            }
            //Đến
            if($input['tgnhapden']!=null){
                $_sql=$_sql." and hsgiahanghoa.tgnhap <='".date('Y-m-d',strtotime($input['tgnhapden']))."'";
            }
            //Loại giá(error Không biết vì sao)
            //$_sql=$input['maloaigia']!=null? $_sql." and hsgiahhtn.maloaigia = ".$input['maloaigia']:$_sql;
            //Loại hàng hóa(error Không biết vì sao)
            //$_sql=$input['maloaihh']!=null? $_sql." and hsgiahhtn.maloaihh = ".$input['maloaihh']:$_sql;
            //Tên hàng hóa
            $_sql=$input['mahh']!=null? $_sql." and giahanghoa.mahh = '".$input['mahh']."'":$_sql;

            //Thị trường nhập
            $_sql=$input['thitruong']!=null? $_sql." and hsgiahanghoa.thitruong = '".$input['thitruong']."'":$_sql;
            //Giá trị tài sản
            //Từ
            if(getDouble($input['giatritu'])>0)
                $_sql=$_sql." and giahanghoa.giatu >= ".getDouble($input['giatritu']);
            //Đến
            if(getDouble($input['giatriden'])>0)
                $_sql=$_sql." and giahanghoa.giaden <= ".getDouble($input['giatriden']);

            $model =  DB::select(DB::raw($_sql));
            //dd($model);

            $modelpb = TtPhongBan::all();

            $dmhanghoa = array_column(DmHangHoa::select('mahh','tenhh')->get()->toarray(),'tenhh','mahh');
            $dmpb = array_column(TtPhongBan::select('ma','ten')->get()->toarray(),'ten','ma');

            foreach($model as $ct){
                $ct->tenhh=$dmhanghoa[$ct->mahh];
                $ct->tenpb =$dmpb[$ct->mahuyen];
            }

            return view('manage.giahhdv.hhdp_laocai.search.index')
                ->with('model',$model)
                ->with('pageTitle','Thông tin giá hàng hóa, dịch vụ');
        }else
            return view('errors.notlogin');
    }
}
