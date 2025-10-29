/**
 * eClass - Training Academy CRM
 * Admin JavaScript
 */

(function($) {
    'use strict';

    // ========================================
    // Utility Functions
    // ========================================
    
    // Show message notification
    window.eclassShowMessage = function(message, type = 'success') {
        // Remove existing messages
        $('.eclass-message').remove();
        
        // Create message element
        const messageClass = type === 'success' ? 'eclass-message-success' : 'eclass-message-error';
        const icon = type === 'success' ? '✓' : '✕';
        
        const messageHtml = `
            <div class="eclass-message ${messageClass}">
                <span class="eclass-message-icon">${icon}</span>
                <span class="eclass-message-text">${message}</span>
            </div>
        `;
        
        // Append to body
        $('body').append(messageHtml);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            $('.eclass-message').fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    };

    // ========================================
    // Students Management
    // ========================================
    
    // Show student modal
    window.eclassShowStudentModal = function(id = null) {
        const modal = $('#eclass-student-modal');
        const form = $('#eclass-student-form');
        
        if (id) {
            $('#eclass-student-modal-title').text(eclassData.editStudent || 'Edit Student');
            eclassLoadStudent(id);
        } else {
            $('#eclass-student-modal-title').text(eclassData.addStudent || 'Add Student');
            form[0].reset();
            $('#student-id').val('');
        }
        
        modal.addClass('active');
    };
    
    // Close student modal
    window.eclassCloseStudentModal = function() {
        $('#eclass-student-modal').removeClass('active');
    };
    
    // Edit student
    window.eclassEditStudent = function(id) {
        eclassShowStudentModal(id);
    };
    
    // Load student data
    function eclassLoadStudent(id) {
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_get_student',
                nonce: eclassData.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const student = response.data;
                    $('#student-id').val(student.id);
                    $('#student-name').val(student.name);
                    $('#student-email').val(student.email);
                    $('#student-phone').val(student.phone);
                    $('#student-course').val(student.course_id);
                    $('#student-status').val(student.enrollment_status);
                    $('#student-enrollment-date').val(student.enrollment_date);
                    $('#student-notes').val(student.notes);
                }
            }
        });
    }
    
    // Delete student
    window.eclassDeleteStudent = function(id) {
        if (!confirm(eclassData.confirmDelete)) {
            return;
        }
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_delete_student',
                nonce: eclassData.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    };
    
    // Save student form
    $('#eclass-student-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: formData + '&action=eclass_save_student&nonce=' + eclassData.nonce,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(eclassData.error);
                }
            }
        });
    });
    
    // ========================================
    // Courses Management
    // ========================================
    
    // Show course modal
    window.eclassShowCourseModal = function(id = null) {
        const modal = $('#eclass-course-modal');
        const form = $('#eclass-course-form');
        
        if (id) {
            $('#eclass-course-modal-title').text(eclassData.editCourse || 'Edit Course');
            eclassLoadCourse(id);
        } else {
            $('#eclass-course-modal-title').text(eclassData.addCourse || 'Add Course');
            form[0].reset();
            $('#course-id').val('');
        }
        
        modal.addClass('active');
        eclassToggleCourseTypeField();
    };
    
    // Close course modal
    window.eclassCloseCourseModal = function() {
        $('#eclass-course-modal').removeClass('active');
    };
    
    // Edit course
    window.eclassEditCourse = function(id) {
        eclassShowCourseModal(id);
    };
    
    // Load course data
    function eclassLoadCourse(id) {
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_get_course',
                nonce: eclassData.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const course = response.data;
                    $('#course-id').val(course.id);
                    $('#course-name').val(course.name);
                    $('#course-instructor').val(course.instructor_id);
                    $('#course-type').val(course.course_type);
                    $('#course-location').val(course.location_or_link);
                    $('#course-schedule').val(course.schedule);
                    $('#course-capacity').val(course.capacity);
                    $('#course-start-date').val(course.start_date);
                    $('#course-end-date').val(course.end_date);
                    $('#course-price').val(course.price);
                    $('#course-status').val(course.status);
                    $('#course-description').val(course.description);
                    eclassToggleCourseTypeField();
                }
            }
        });
    }
    
    // Toggle course type field label
    window.eclassToggleCourseTypeField = function() {
        const type = $('#course-type').val();
        const label = $('#course-location-label');
        const input = $('#course-location');
        
        if (type === 'online') {
            label.text(eclassData.meetingLink || 'Meeting Link');
            input.attr('placeholder', eclassData.enterMeetingLink || 'Enter meeting link (e.g., Zoom, Teams)');
        } else {
            label.text(eclassData.locationRoom || 'Location/Room');
            input.attr('placeholder', eclassData.enterRoomName || 'Enter room name or location');
        }
    };
    
    // Delete course
    window.eclassDeleteCourse = function(id) {
        if (!confirm(eclassData.confirmDelete)) {
            return;
        }
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_delete_course',
                nonce: eclassData.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    };
    
    // Save course form
    $('#eclass-course-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: formData + '&action=eclass_save_course&nonce=' + eclassData.nonce,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(eclassData.error);
                }
            }
        });
    });
    
    // ========================================
    // Instructors Management
    // ========================================
    
    // Show instructor modal
    window.eclassShowInstructorModal = function(id = null) {
        const modal = $('#eclass-instructor-modal');
        const form = $('#eclass-instructor-form');
        
        if (id) {
            $('#eclass-instructor-modal-title').text(eclassData.editInstructor || 'Edit Team Member');
            eclassLoadInstructor(id);
        } else {
            $('#eclass-instructor-modal-title').text(eclassData.addInstructor || 'Add Team Member');
            form[0].reset();
            $('#instructor-id').val('');
        }
        
        modal.addClass('active');
    };
    
    // Close instructor modal
    window.eclassCloseInstructorModal = function() {
        $('#eclass-instructor-modal').removeClass('active');
    };
    
    // Edit instructor
    window.eclassEditInstructor = function(id) {
        eclassShowInstructorModal(id);
    };
    
    // Load instructor data
    function eclassLoadInstructor(id) {
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_get_instructor',
                nonce: eclassData.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const instructor = response.data;
                    $('#instructor-id').val(instructor.id);
                    $('#instructor-name').val(instructor.name);
                    $('#instructor-email').val(instructor.email);
                    $('#instructor-phone').val(instructor.phone);
                    $('#instructor-role').val(instructor.role);
                    $('#instructor-specialization').val(instructor.specialization);
                    $('#instructor-bio').val(instructor.bio);
                }
            }
        });
    }
    
    // Delete instructor
    window.eclassDeleteInstructor = function(id) {
        if (!confirm(eclassData.confirmDelete)) {
            return;
        }
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_delete_instructor',
                nonce: eclassData.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    };
    
    // Save instructor form
    $('#eclass-instructor-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: formData + '&action=eclass_save_instructor&nonce=' + eclassData.nonce,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(eclassData.error);
                }
            }
        });
    });
    
    // ========================================
    // Billing Management
    // ========================================
    
    // Show billing modal
    window.eclassShowBillingModal = function(id = null) {
        const modal = $('#eclass-billing-modal');
        const form = $('#eclass-billing-form');
        
        if (id) {
            $('#eclass-billing-modal-title').text(eclassData.editBilling || 'Edit Invoice');
            eclassLoadBilling(id);
        } else {
            $('#eclass-billing-modal-title').text(eclassData.addBilling || 'Add Invoice');
            form[0].reset();
            $('#billing-id').val('');
            // Generate invoice number
            const invoiceNum = 'INV-' + Date.now().toString().substr(-6);
            $('#billing-invoice-number').val(invoiceNum);
        }
        
        modal.addClass('active');
        eclassTogglePaymentFields();
    };
    
    // Close billing modal
    window.eclassCloseBillingModal = function() {
        $('#eclass-billing-modal').removeClass('active');
    };
    
    // Edit billing
    window.eclassEditBilling = function(id) {
        eclassShowBillingModal(id);
    };
    
    // Load billing data
    function eclassLoadBilling(id) {
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_get_billing',
                nonce: eclassData.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const billing = response.data;
                    $('#billing-id').val(billing.id);
                    $('#billing-invoice-number').val(billing.invoice_number);
                    $('#billing-student').val(billing.student_id);
                    $('#billing-course').val(billing.course_id);
                    $('#billing-amount').val(billing.amount);
                    $('#billing-due-date').val(billing.due_date);
                    $('#billing-status').val(billing.payment_status);
                    $('#billing-method').val(billing.payment_method);
                    $('#billing-transaction-code').val(billing.transaction_code);
                    if (billing.payment_date) {
                        $('#billing-payment-date').val(billing.payment_date.replace(' ', 'T'));
                    }
                    $('#billing-notes').val(billing.notes);
                    eclassTogglePaymentFields();
                }
            }
        });
    }
    
    // Toggle payment fields
    window.eclassTogglePaymentFields = function() {
        const status = $('#billing-status').val();
        const paymentDetails = $('#payment-details');
        
        if (status === 'paid') {
            paymentDetails.show();
        } else {
            paymentDetails.hide();
        }
    };
    
    // Delete billing
    window.eclassDeleteBilling = function(id) {
        if (!confirm(eclassData.confirmDelete)) {
            return;
        }
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_delete_billing',
                nonce: eclassData.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    };
    
    // Save billing form
    $('#eclass-billing-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: formData + '&action=eclass_save_billing&nonce=' + eclassData.nonce,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(eclassData.error);
                }
            }
        });
    });
    
    // ========================================
    // Import Modal
    // ========================================
    
    window.eclassShowImportModal = function() {
        $('#eclass-import-modal').addClass('active');
    };
    
    window.eclassCloseImportModal = function() {
        $('#eclass-import-modal').removeClass('active');
    };
    
    // ========================================
    // Sample Data Management
    // ========================================
    
    window.eclassInsertSampleData = function() {
        if (!confirm('هل أنت متأكد من إدراج البيانات التجريبية؟\nسيتم إضافة مدربين، دورات، طلاب، وفواتير تجريبية.')) {
            return;
        }
        
        const messageDiv = $('#sample-data-message');
        messageDiv.html('<div class="notice notice-info"><p>جاري إدراج البيانات...</p></div>');
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_insert_sample_data',
                nonce: eclassData.nonce
            },
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    messageDiv.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                messageDiv.html('<div class="notice notice-error"><p>حدث خطأ أثناء إدراج البيانات</p></div>');
            }
        });
    };
    
    window.eclassDeleteAllData = function() {
        if (!confirm('تحذير: هل أنت متأكد من حذف جميع البيانات؟\n\nسيتم حذف:\n- جميع الطلاب\n- جميع الدورات\n- جميع المدربين\n- جميع الفواتير\n\nهذا الإجراء لا يمكن التراجع عنه!')) {
            return;
        }
        
        if (!confirm('تأكيد نهائي: هل أنت متأكد تماماً؟')) {
            return;
        }
        
        const messageDiv = $('#sample-data-message');
        messageDiv.html('<div class="notice notice-warning"><p>جاري حذف البيانات...</p></div>');
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_delete_all_data',
                nonce: eclassData.nonce
            },
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    messageDiv.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                messageDiv.html('<div class="notice notice-error"><p>حدث خطأ أثناء حذف البيانات</p></div>');
            }
        });
    };
    
    // ========================================
    // Close modals on outside click
    // ========================================
    
    $('.eclass-modal').on('click', function(e) {
        if ($(e.target).hasClass('eclass-modal')) {
            $(this).removeClass('active');
        }
    });
    
    // Close modals on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.eclass-modal').removeClass('active');
        }
    });
    
    // ========================================
    // Enrollments Management
    // ========================================
    
    window.eclassManageEnrollments = function(studentId, studentName) {
        $('#enrollment-student-id').val(studentId);
        $('#eclass-enrollments-modal-title').text('إدارة تسجيلات: ' + studentName);
        $('#eclass-enrollments-modal').addClass('active');
        eclassLoadEnrollments(studentId);
    };
    
    window.eclassCloseEnrollmentsModal = function() {
        $('#eclass-enrollments-modal').removeClass('active');
        $('#new-enrollment-course').val('');
        $('#new-enrollment-status').val('active');
    };
    
    window.eclassLoadEnrollments = function(studentId) {
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_get_student_courses',
                nonce: eclassData.nonce,
                student_id: studentId
            },
            success: function(response) {
                if (response.success) {
                    const enrollments = response.data;
                    let html = '';
                    
                    if (enrollments.length === 0) {
                        html = '<p class="eclass-text-muted">لا توجد تسجيلات حالياً</p>';
                    } else {
                        html = '<table class="eclass-table"><thead><tr>';
                        html += '<th>الدورة</th><th>الحالة</th><th>تاريخ التسجيل</th><th>إجراءات</th>';
                        html += '</tr></thead><tbody>';
                        
                        enrollments.forEach(function(enrollment) {
                            html += '<tr>';
                            html += '<td><strong>' + enrollment.course_name + '</strong></td>';
                            html += '<td><span class="eclass-badge eclass-badge-' + enrollment.enrollment_status + '">' + enrollment.enrollment_status + '</span></td>';
                            html += '<td>' + enrollment.enrollment_date + '</td>';
                            html += '<td class="eclass-actions">';
                            html += '<button class="eclass-btn-icon eclass-btn-danger" onclick="eclassUnenrollStudent(' + studentId + ', ' + enrollment.course_id + ')" title="إلغاء التسجيل">';
                            html += '<span class="dashicons dashicons-trash"></span>';
                            html += '</button>';
                            html += '</td>';
                            html += '</tr>';
                        });
                        
                        html += '</tbody></table>';
                    }
                    
                    $('#eclass-enrollments-list').html(html);
                }
            }
        });
    };
    
    // Add enrollment form submit
    $('#eclass-add-enrollment-form').on('submit', function(e) {
        e.preventDefault();
        
        const studentId = $('#enrollment-student-id').val();
        const courseId = $('#new-enrollment-course').val();
        const status = $('#new-enrollment-status').val();
        
        if (!courseId) {
            alert('الرجاء اختيار دورة');
            return;
        }
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_enroll_student',
                nonce: eclassData.nonce,
                student_id: studentId,
                course_id: courseId,
                status: status,
                notes: ''
            },
            success: function(response) {
                if (response.success) {
                    if (typeof window.eclassShowMessage === 'function') {
                        window.eclassShowMessage(response.data.message, 'success');
                    }
                    $('#new-enrollment-course').val('');
                    $('#new-enrollment-status').val('active');
                    eclassLoadEnrollments(studentId);
                    // Reload page to update counts
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    if (typeof window.eclassShowMessage === 'function') {
                        window.eclassShowMessage(response.data || 'حدث خطأ', 'error');
                    } else {
                        alert(response.data || 'حدث خطأ');
                    }
                }
            }
        });
    });
    
    window.eclassUnenrollStudent = function(studentId, courseId) {
        if (!confirm('هل أنت متأكد من إلغاء تسجيل الطالب من هذه الدورة؟')) {
            return;
        }
        
        $.ajax({
            url: eclassData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'eclass_unenroll_student',
                nonce: eclassData.nonce,
                student_id: studentId,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    if (typeof window.eclassShowMessage === 'function') {
                        window.eclassShowMessage(response.data.message, 'success');
                    }
                    eclassLoadEnrollments(studentId);
                    // Reload page to update counts
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    if (typeof window.eclassShowMessage === 'function') {
                        window.eclassShowMessage(response.data || 'حدث خطأ', 'error');
                    } else {
                        alert(response.data || 'حدث خطأ');
                    }
                }
            }
        });
    };

/* ========================================
    Enhanced Pagination JavaScript
    ======================================== */

(function($) {
    'use strict';

    // Enhanced pagination functionality
    window.eclassEnhancedPagination = {

        // Debug function to check if functions are available
        debug: function() {
            console.log('eclassEnhancedPagination debug:');
            console.log('- eclassEditStudent:', typeof window.eclassEditStudent);
            console.log('- eclassDeleteStudent:', typeof window.eclassDeleteStudent);
            console.log('- eclassManageEnrollments:', typeof window.eclassManageEnrollments);
            console.log('- eclassShowMessage:', typeof window.eclassShowMessage);
        },

        init: function() {
            this.injectStyles();
            this.bindEvents();
            this.enhanceAccessibility();
            this.handleResponsiveDesign();
        },

        bindEvents: function() {
            // Smooth scrolling for pagination links only (not action buttons)
            $(document).on('click', '.eclass-pagination a.eclass-btn-link, .eclass-pagination a.eclass-btn-secondary', this.handlePaginationClick);

            // Keyboard navigation
            $(document).on('keydown', '.eclass-pagination', this.handleKeyboardNavigation);

            // Loading states for pagination only
            $(document).on('click', '.eclass-pagination a.eclass-btn-link, .eclass-pagination a.eclass-btn-secondary', this.showLoadingState);
        },

        handlePaginationClick: function(e) {
            // Only handle pagination links, not action buttons
            if (!$(this).hasClass('eclass-btn-link') && !$(this).hasClass('eclass-btn-secondary')) {
                return;
            }

            // Add loading state
            var $pagination = $(this).closest('.eclass-pagination');
            $pagination.addClass('eclass-pagination-loading');

            // Smooth scroll to top of table (optional)
            if (eclassEnhancedPagination.shouldScrollToTop()) {
                $('html, body').animate({
                    scrollTop: $('.eclass-card').offset().top - 20
                }, 300);
            }
        },

        showLoadingState: function() {
            // Only apply to pagination buttons
            if (!$(this).hasClass('eclass-btn-link') && !$(this).hasClass('eclass-btn-secondary')) {
                return;
            }

            var $btn = $(this);
            var originalText = $btn.html();

            // Add loading spinner
            $btn.addClass('eclass-btn-loading');
            $btn.html('<span class="eclass-spinner"></span>' + originalText);

            // Remove loading state after navigation
            setTimeout(function() {
                $btn.removeClass('eclass-btn-loading');
                $btn.html(originalText);
            }, 1000);
        },

        handleKeyboardNavigation: function(e) {
            var $pagination = $(this);
            var $links = $pagination.find('a.eclass-btn-link:not(.eclass-btn-disabled), a.eclass-btn-secondary:not(.eclass-btn-disabled)');
            var $current = $pagination.find('[aria-current="page"]');

            switch(e.keyCode) {
                case 37: // Left arrow
                case 72: // H key (vim-style)
                    e.preventDefault();
                    var $prev = $current.prev('a.eclass-btn-link, a.eclass-btn-secondary');
                    if ($prev.length) {
                        $prev[0].click();
                    } else {
                        $links.first().focus();
                    }
                    break;

                case 39: // Right arrow
                case 76: // L key (vim-style)
                    e.preventDefault();
                    var $next = $current.next('a.eclass-btn-link, a.eclass-btn-secondary');
                    if ($next.length) {
                        $next[0].click();
                    } else {
                        $links.last().focus();
                    }
                    break;

                case 36: // Home key
                    e.preventDefault();
                    $links.first().focus();
                    break;

                case 35: // End key
                    e.preventDefault();
                    $links.last().focus();
                    break;
            }
        },

        enhanceAccessibility: function() {
            // Add live region for screen readers
            if (!$('#eclass-pagination-live-region').length) {
                $('body').append('<div id="eclass-pagination-live-region" aria-live="polite" aria-atomic="true" class="screen-reader-text"></div>');
            }

            // Enhance focus management for pagination only
            $(document).on('focus', '.eclass-pagination a.eclass-btn-link, .eclass-pagination a.eclass-btn-secondary', function() {
                $(this).addClass('eclass-focus-visible');
            });

            $(document).on('blur', '.eclass-pagination a.eclass-btn-link, .eclass-pagination a.eclass-btn-secondary', function() {
                $(this).removeClass('eclass-focus-visible');
            });
        },

        shouldScrollToTop: function() {
            // Check if pagination is at bottom of long table
            return $('.eclass-pagination').offset().top > $(window).height() / 2;
        },

        updateLiveRegion: function(message) {
            $('#eclass-pagination-live-region').text(message);
        }
    };

    // Initialize enhanced pagination
    $(document).ready(function() {
        // Initialize main eClass functions first
        console.log('eClass Admin JS loaded');

        // Debug: Check if main functions are available
        setTimeout(function() {
            if (typeof window.eclassEnhancedPagination !== 'undefined') {
                window.eclassEnhancedPagination.debug();
            }
        }, 100);

        // Then initialize enhanced pagination
        if (typeof window.eclassEnhancedPagination !== 'undefined') {
            window.eclassEnhancedPagination.init();
            console.log('Enhanced pagination initialized');
        }
    });

})(jQuery);

    // Enhanced pagination styles are now injected via the injectStyles() method
    // No additional styles needed here

})(jQuery);
