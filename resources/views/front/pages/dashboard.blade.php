@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;
?>
<h2>Dashboard</h2>
<!--<h6 class="mb-4 text-danger">Developer's Note : ID should be : NVGI/{Unit Name}{1st letter of branch}/1,2,3...</h6>-->
<div class="card shadow bg-light mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow bg-info ">
                    <div class="card-body text-center text-white">
                        <h5>VHS Bibirhat</h5>
                        <h2>19</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card shadow bg-info ">
                    <div class="card-body text-center text-white">
                        <h5>VHS Rajarhat</h5>
                        <h2>4</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow bg-info ">
                    <div class="card-body text-center text-white">
                        <h5>TSA Bibirhat</h5>
                        <h2>239</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow bg-info ">
                    <div class="card-body text-center text-white">
                        <h5>TSA Mukundapur</h5>
                        <h2>6</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow bg-warning ">
                    <div class="card-body text-center text-white">
                        <h5>Total VHS Students : 23</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow bg-warning ">
                    <div class="card-body text-center text-white">
                        <h5>Total TSA Students : 245</h5>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<div class="card shadow bg-light">
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <div class="card shadow bg-success ">
                    <div class="card-body text-center text-white">
                        <h5>Jayeeta</h5>
                        <h2>126</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card shadow bg-success ">
                    <div class="card-body text-center text-white">
                        <h5>Swastika</h5>
                        <h2>4</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card shadow bg-success ">
                    <div class="card-body text-center text-white">
                        <h5>Sahana</h5>
                        <h2>239</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card shadow bg-success ">
                    <div class="card-body text-center text-white">
                        <h5>Abirlal</h5>
                        <h2>6</h2>
                    </div>
                </div>
            </div>


            <div class="col-md-2">
                <div class="card shadow bg-success ">
                    <div class="card-body text-center text-white">
                        <h5>Sweety</h5>
                        <h2>6</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card shadow bg-success ">
                    <div class="card-body text-center text-white">
                        <h5>Ruma</h5>
                        <h2>6</h2>
                    </div>
                </div>
            </div>



        </div>

    </div>
</div>
@endsection