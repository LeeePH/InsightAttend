      <!-- ========== Left Sidebar Start ========== -->
            <div class="left side-menu">
                <div class="slimscroll-menu" id="remove-scroll">

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">
                        
                        <!-- Left Menu Start -->
                        <ul class="metismenu" id="side-menu">
                            @auth
                                @if(auth()->user()->hasRole('admin'))
                            <li class="menu-title">Main</li>
                            <li class="">
                                <a href="{{route('admin')}}" class="waves-effect {{ request()->is("admin") || request()->is("admin/*") ? "mm active" : "" }}">
                                    <i class="ti-home"></i> <span> Dashboard </span>
                                </a>
                            </li>
                            

                            <li class="">
                                <a href="/employees" class="waves-effect {{ request()->is("employees") || request()->is("employees/*") ? "mm active" : "" }}">
                                    <i class="ti-user"></i> <span> Manage Employee </span>
                                </a>
                            </li>

                            <li class="menu-title">Management</li>

                            <li class="">
                                <a href="{{ route('employee_timetable.index') }}" class="waves-effect {{ request()->is("employee-timetable") || request()->is("employee-timetable/*") ? "mm active" : "" }}">
                                    <i class="ti-layout-grid3-alt"></i> <span> Schedule Management </span>
                                </a>
                            </li>
                            <li class="">
                                <a href="{{ route('class_sections.index') }}" class="waves-effect {{ request()->is('class-sections') ? 'mm active' : '' }}">
                                    <i class="ti-bookmark-alt"></i> <span> Class sections </span>
                                </a>
                            </li>
                            <li class="">
                                <a href="{{ route('departments.report') }}" class="waves-effect {{ request()->is('department-reports') ? 'mm active' : '' }}">
                                    <i class="ti-layers"></i> <span> Department Management </span>
                                </a>
                            </li>
                            <li class="">
                                <a href="/check" class="waves-effect {{ request()->is("check") || request()->is("check/*") ? "mm active" : "" }}">
                                    <i class="dripicons-to-do"></i> <span> Attendance Sheet </span>
                                </a>
                            </li>

                            <li class="">
                                <a href="/attendance" class="waves-effect {{ request()->is("attendance") || request()->is("attendance/*") ? "mm active" : "" }}">
                                    <i class="ti-calendar"></i> <span> Attendance Logs </span>
                                </a>
                            </li>
                            <li class="{{ (request()->is('leave') || request()->is('leave/*') || request()->is('resignation') || request()->is('resignation/*') || request()->is('loan') || request()->is('loan/*') || request()->is('discount') || request()->is('discount/*') || request()->is('overtime-authorization') || request()->is('overtime-authorization/*') || request()->is('undertime-authorization') || request()->is('undertime-authorization/*') || request()->is('permit-to-teach-outside') || request()->is('permit-to-teach-outside/*') || request()->is('subsitution') || request()->is('subsitution/*')) ? 'mm-active' : '' }}">
                                <a href="javascript:void(0);" class="has-arrow waves-effect {{ (request()->is('leave') || request()->is('leave/*') || request()->is('resignation') || request()->is('resignation/*') || request()->is('loan') || request()->is('loan/*') || request()->is('discount') || request()->is('discount/*') || request()->is('overtime-authorization') || request()->is('overtime-authorization/*') || request()->is('undertime-authorization') || request()->is('undertime-authorization/*') || request()->is('permit-to-teach-outside') || request()->is('permit-to-teach-outside/*') || request()->is('subsitution') || request()->is('subsitution/*')) ? 'mm active' : '' }}">
                                    <i class="dripicons-backspace"></i> <span> Requests </span>
                                </a>
                                <ul class="submenu" aria-expanded="false">
                                    <li>
                                        <a href="/leave" class="waves-effect {{ (request()->is('leave') || request()->is('leave/*')) ? 'mm active' : '' }}">
                                            <span>Leave</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('resignation.admin') }}" class="waves-effect {{ (request()->is('resignation') || request()->is('resignation/*')) ? 'mm active' : '' }}">
                                            <span>Resignation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('loan.admin') }}" class="waves-effect {{ (request()->is('loan') || request()->is('loan/*')) ? 'mm active' : '' }}">
                                            <span>Loan</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('discount.admin') }}" class="waves-effect {{ (request()->is('discount') || request()->is('discount/*')) ? 'mm active' : '' }}">
                                            <span>Discount</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('overtime_authorization.admin') }}" class="waves-effect {{ (request()->is('overtime-authorization') || request()->is('overtime-authorization/*')) ? 'mm active' : '' }}">
                                            <span>Overtime Authorization</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('undertime_authorization.admin') }}" class="waves-effect {{ (request()->is('undertime-authorization') || request()->is('undertime-authorization/*')) ? 'mm active' : '' }}">
                                            <span>Undertime Authorization</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('permit_to_teach_outside.admin') }}" class="waves-effect {{ (request()->is('permit-to-teach-outside') || request()->is('permit-to-teach-outside/*')) ? 'mm active' : '' }}">
                                            <span>Permit to Teach Outside</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('substitution.admin') }}" class="waves-effect {{ (request()->is('subsitution') || request()->is('subsitution/*')) ? 'mm active' : '' }}">
                                            <span>Subsitution Form</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="{{ (request()->is('admin/maintenance-form') || request()->is('admin/feedback') || request()->is('admin/backups') || request()->is('admin/user-management') || request()->is('admin/audit-logs')) ? 'mm-active' : '' }}">
                                <a href="javascript:void(0);" class="has-arrow waves-effect {{ (request()->is('admin/maintenance-form') || request()->is('admin/backups') || request()->is('admin/user-management') || request()->is('admin/audit-logs')) ? 'mm active' : '' }}">
                                    <i class="fa fa-cog"></i> <span> Settings </span>
                                </a>
                                <ul class="submenu" aria-expanded="false">
                                    <li>
                                        <a href="{{ route('admin.maintenance_form') }}" class="waves-effect {{ request()->is('admin/maintenance-form') ? 'mm active' : '' }}">
                                            <span>Maintenance Form</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.backups') }}" class="waves-effect {{ request()->is('admin/backups') ? 'mm active' : '' }}">
                                            <span>Admin Management</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.user_management') }}" class="waves-effect {{ request()->is('admin/user-management') ? 'mm active' : '' }}">
                                            <span>User Management</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.audit_logs') }}" class="waves-effect {{ request()->is('admin/audit-logs') ? 'mm active' : '' }}">
                                            <span>Audit Trail</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            {{-- <li class="menu-title">Tools</li>
                            <li class="">
                                <a href="{{ route("finger_device.index") }}" class="waves-effect {{ request()->is("finger_device") || request()->is("finger_device/*") ? "mm active" : "" }}">
                                    <i class="fas fa-fingerprint"></i> <span> Biometric Device </span>
                                </a>
                            </li> --}}
                                @elseif(auth()->user()->hasAnyRole(['hr', 'secretary']))
                            <li class="menu-title">Scheduling</li>
                            <li class="">
                                <a href="{{ route('employee_timetable.index') }}" class="waves-effect {{ request()->is("employee-timetable") || request()->is("employee-timetable/*") ? "mm active" : "" }}">
                                    <i class="ti-layout-grid3-alt"></i> <span> Schedule Management </span>
                                </a>
                            </li>
                            <li class="">
                                <a href="{{ route('class_sections.index') }}" class="waves-effect {{ request()->is('class-sections') ? 'mm active' : '' }}">
                                    <i class="ti-bookmark-alt"></i> <span> Class sections </span>
                                </a>
                            </li>
                            @if(auth()->user()->hasRole('hr'))
                            <li class="">
                                <a href="{{ route('admin.maintenance_form') }}" class="waves-effect {{ request()->is('admin/maintenance-form') ? 'mm active' : '' }}">
                                    <i class="fa fa-sliders"></i> <span> Maintenance Form </span>
                                </a>
                            </li>
                            @endif
                            <li class="menu-title">Account</li>
                            <li class="">
                                <a href="{{ route('profile') }}" class="waves-effect {{ request()->is("profile") ? "mm active" : "" }}">
                                    <i class="mdi mdi-account-circle"></i> <span> Profile </span>
                                </a>
                            </li>
                                @elseif(auth()->user()->hasRole('employee'))
                            <li class="menu-title">Employee Menu</li>
                            <li class="">
                                <a href="{{route('employee.dashboard')}}" class="waves-effect {{ request()->is("employee/dashboard") ? "mm active" : "" }}">
                                    <i class="ti-home"></i><span> Dashboard </span>
                                </a>
                            </li>
                            <li class="">
                                <a href="{{ route('employee.attendance_logs') }}" class="waves-effect {{ request()->is("employee/attendance-logs") ? "mm active" : "" }}">
                                    <i class="ti-calendar"></i> <span> Attendance Logs </span>
                                </a>
                            </li>
                            <li class="{{ (request()->is('leave/request') || request()->is('resignation/request') || request()->is('loan/request') || request()->is('discount/request') || request()->is('overtime-authorization/request') || request()->is('undertime-authorization/request') || request()->is('permit-to-teach-outside/request') || request()->is('subsitution/request')) ? 'mm-active' : '' }}">
                                <a href="javascript:void(0);" class="has-arrow waves-effect {{ (request()->is('leave/request') || request()->is('resignation/request') || request()->is('loan/request') || request()->is('discount/request') || request()->is('overtime-authorization/request') || request()->is('undertime-authorization/request') || request()->is('permit-to-teach-outside/request') || request()->is('subsitution/request')) ? 'mm active' : '' }}">
                                    <i class="dripicons-backspace"></i> <span> Requests </span>
                                </a>
                                <ul class="submenu" aria-expanded="false">
                                    <li>
                                        <a href="{{ route('leave.request') }}" class="waves-effect {{ request()->is('leave/request') ? 'mm active' : '' }}">
                                            <span>Request Leave</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('resignation.request') }}" class="waves-effect {{ request()->is('resignation/request') ? 'mm active' : '' }}">
                                            <span>Resignation Request</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('loan.request') }}" class="waves-effect {{ request()->is('loan/request') ? 'mm active' : '' }}">
                                            <span>Company Loan Application</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('discount.request') }}" class="waves-effect {{ request()->is('discount/request') ? 'mm active' : '' }}">
                                            <span>Application for Discount</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('overtime_authorization.request') }}" class="waves-effect {{ request()->is('overtime-authorization/request') ? 'mm active' : '' }}">
                                            <span>Overtime Authorization Form</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('undertime_authorization.request') }}" class="waves-effect {{ request()->is('undertime-authorization/request') ? 'mm active' : '' }}">
                                            <span>Undertime Authorization Form</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('permit_to_teach_outside.request') }}" class="waves-effect {{ request()->is('permit-to-teach-outside/request') ? 'mm active' : '' }}">
                                            <span>Permit to Teach Outside Form</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('substitution.request') }}" class="waves-effect {{ request()->is('subsitution/request') ? 'mm active' : '' }}">
                                            <span>Subsitution Form</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="">
                                <a href="{{ route('employee.settings') }}" class="waves-effect {{ request()->is("employee/settings") ? "mm active" : "" }}">
                                    <i class="fa fa-cog"></i> <span> Account Settings </span>
                                </a>
                            </li>
                                @endif
                            @endauth

                        </ul>

                    </div>
                    <!-- Sidebar -->
                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>
            <!-- Left Sidebar End -->
