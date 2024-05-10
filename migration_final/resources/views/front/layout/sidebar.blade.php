@ -1,7 +1,7 @@
{{-- Correcting issues in the Skydash Admin Panel Sidebar using Session --}}


<!-- partial:partials/_sidebar.html -->

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
@ -39,6 +39,9 @@
                </a>
                <div class="collapse" id="ui-catalogue">
                    <ul class="nav flex-column sub-menu" style="background: #fff !important; color: #052CA3 !important">
                        <li class="nav-item"> <a @if (Session::get('page') == 'sections')   style="background: #052CA3 !important; color: #FFF !important" @else style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link" href="{{ url('admin/sections') }}">Sections</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'categories') style="background: #052CA3 !important; color: #FFF !important" @else style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link" href="{{ url('admin/categories') }}">Categories</a></li>
                        <li class="nav-item"> <a @if (Session::get('page') == 'brands')     style="background: #052CA3 !important; color: #FFF !important" @else style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link" href="{{ url('admin/brands') }}">Brands</a></li> 
                        <li class="nav-item"> <a @if (Session::get('page') == 'products')   style="background: #052CA3 !important; color: #FFF !important" @else style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link" href="{{ url('admin/products') }}">Products</a></li>
                        
                    </ul>
@ -107,7 +110,7 @@
                        <li class="nav-item"> <a @if (Session::get('page') == 'brands')     style="background: #052CA3 !important; color: #FFF !important" @else style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link" href="{{ url('admin/brands') }}">Brands</a></li> 
                        <li class="nav-item"> <a @if (Session::get('page') == 'products')   style="background: #052CA3 !important; color: #FFF !important" @else style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link" href="{{ url('admin/products') }}">Products</a></li>
                        
                        <li class="nav-item"> <a @if (Session::get('page') == 'filters')    style="background: #052CA3 !important; color: #FFF !important" @else style="background: #fff !important; color: #052CA3 !important" @endif class="nav-link" href="{{ url('admin/filters') }}">Filters</a></li>
                       
                    </ul>
                </div>
            </li>
@ -172,18 +175,7 @@
            </li>
        @endif

