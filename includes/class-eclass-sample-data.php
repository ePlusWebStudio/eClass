<?php
/**
 * Sample Data Generator Class
 * إنشاء بيانات تجريبية للاختبار
 */

if (!defined('ABSPATH')) {
    exit;
}

class EClass_Sample_Data {
    
    /**
     * Insert sample data
     */
    public static function insert_sample_data() {
        global $wpdb;
        
        // Check if data already exists
        $existing_students = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}eclass_students");
        if ($existing_students > 0) {
            return array(
                'success' => false,
                'message' => __('Sample data already exists. Please delete existing data first.', 'eclass')
            );
        }
        
        // Insert Instructors
        $instructors = array(
            array(
                'name' => 'أحمد محمد',
                'email' => 'ahmed@example.com',
                'phone' => '0501234567',
                'role' => 'instructor',
                'specialization' => 'تطوير الويب والبرمجة',
                'bio' => 'مدرب متخصص في تطوير الويب مع خبرة 10 سنوات'
            ),
            array(
                'name' => 'فاطمة علي',
                'email' => 'fatima@example.com',
                'phone' => '0507654321',
                'role' => 'instructor',
                'specialization' => 'تحليل البيانات والذكاء الاصطناعي',
                'bio' => 'خبيرة في علوم البيانات والتعلم الآلي'
            ),
            array(
                'name' => 'خالد السعيد',
                'email' => 'khaled@example.com',
                'phone' => '0509876543',
                'role' => 'instructor',
                'specialization' => 'التسويق الرقمي',
                'bio' => 'مستشار تسويق رقمي معتمد'
            ),
            array(
                'name' => 'سارة أحمد',
                'email' => 'sara@example.com',
                'phone' => '0503456789',
                'role' => 'admin',
                'specialization' => 'إدارة الأكاديمية',
                'bio' => 'مديرة العمليات والشؤون الإدارية'
            ),
            array(
                'name' => 'محمد حسن',
                'email' => 'mohammed@example.com',
                'phone' => '0502345678',
                'role' => 'support',
                'specialization' => 'الدعم الفني',
                'bio' => 'مسؤول الدعم الفني والمساعدة'
            )
        );
        
        $instructor_ids = array();
        foreach ($instructors as $instructor) {
            $wpdb->insert($wpdb->prefix . 'eclass_instructors', $instructor);
            $instructor_ids[] = $wpdb->insert_id;
        }
        
        // Insert Courses
        $courses = array(
            array(
                'name' => 'تطوير تطبيقات الويب الحديثة',
                'instructor_id' => $instructor_ids[0],
                'schedule' => 'الأحد - الثلاثاء 6:00 - 9:00 مساءً',
                'capacity' => 25,
                'enrolled_count' => 18,
                'status' => 'ongoing',
                'course_type' => 'online',
                'location_or_link' => 'https://zoom.us/j/123456789',
                'price' => 2500.00,
                'description' => 'دورة شاملة في تطوير تطبيقات الويب باستخدام أحدث التقنيات',
                'start_date' => date('Y-m-d', strtotime('-2 weeks')),
                'end_date' => date('Y-m-d', strtotime('+6 weeks'))
            ),
            array(
                'name' => 'علوم البيانات والتحليل',
                'instructor_id' => $instructor_ids[1],
                'schedule' => 'السبت - الاثنين 7:00 - 10:00 مساءً',
                'capacity' => 20,
                'enrolled_count' => 15,
                'status' => 'ongoing',
                'course_type' => 'online',
                'location_or_link' => 'https://teams.microsoft.com/meet/abc123',
                'price' => 3000.00,
                'description' => 'تعلم تحليل البيانات والذكاء الاصطناعي من الصفر',
                'start_date' => date('Y-m-d', strtotime('-1 week')),
                'end_date' => date('Y-m-d', strtotime('+7 weeks'))
            ),
            array(
                'name' => 'التسويق الرقمي المتقدم',
                'instructor_id' => $instructor_ids[2],
                'schedule' => 'الأربعاء - الخميس 5:00 - 8:00 مساءً',
                'capacity' => 30,
                'enrolled_count' => 22,
                'status' => 'ongoing',
                'course_type' => 'offline',
                'location_or_link' => 'قاعة التدريب A - الدور الثاني',
                'price' => 2000.00,
                'description' => 'استراتيجيات التسويق الرقمي ووسائل التواصل الاجتماعي',
                'start_date' => date('Y-m-d', strtotime('-3 weeks')),
                'end_date' => date('Y-m-d', strtotime('+5 weeks'))
            ),
            array(
                'name' => 'البرمجة بلغة Python',
                'instructor_id' => $instructor_ids[0],
                'schedule' => 'الجمعة 4:00 - 8:00 مساءً',
                'capacity' => 20,
                'enrolled_count' => 0,
                'status' => 'upcoming',
                'course_type' => 'online',
                'location_or_link' => 'https://zoom.us/j/987654321',
                'price' => 1800.00,
                'description' => 'تعلم البرمجة بلغة Python للمبتدئين',
                'start_date' => date('Y-m-d', strtotime('+2 weeks')),
                'end_date' => date('Y-m-d', strtotime('+10 weeks'))
            ),
            array(
                'name' => 'تصميم تجربة المستخدم UX/UI',
                'instructor_id' => $instructor_ids[1],
                'schedule' => 'الثلاثاء - الخميس 6:00 - 9:00 مساءً',
                'capacity' => 15,
                'enrolled_count' => 12,
                'status' => 'completed',
                'course_type' => 'offline',
                'location_or_link' => 'استوديو التصميم - الدور الأول',
                'price' => 2200.00,
                'description' => 'أساسيات تصميم واجهات المستخدم وتجربة المستخدم',
                'start_date' => date('Y-m-d', strtotime('-10 weeks')),
                'end_date' => date('Y-m-d', strtotime('-2 weeks'))
            )
        );
        
        $course_ids = array();
        foreach ($courses as $course) {
            $wpdb->insert($wpdb->prefix . 'eclass_courses', $course);
            $course_ids[] = $wpdb->insert_id;
        }
        
        // Insert Students (بدون course_id - سيتم التسجيل في الدورات لاحقاً)
        $students = array(
            array('name' => 'عبدالله محمد', 'email' => 'abdullah@example.com', 'phone' => '0551234567', 'notes' => 'طالب متميز ومجتهد'),
            array('name' => 'نورة أحمد', 'email' => 'noura@example.com', 'phone' => '0557654321', 'notes' => ''),
            array('name' => 'يوسف علي', 'email' => 'yousef@example.com', 'phone' => '0559876543', 'notes' => ''),
            array('name' => 'مريم خالد', 'email' => 'mariam@example.com', 'phone' => '0553456789', 'notes' => 'لديها خلفية في الرياضيات'),
            array('name' => 'سعد عبدالرحمن', 'email' => 'saad@example.com', 'phone' => '0552345678', 'notes' => ''),
            array('name' => 'ريم سعيد', 'email' => 'reem@example.com', 'phone' => '0558765432', 'notes' => ''),
            array('name' => 'فهد ماجد', 'email' => 'fahad@example.com', 'phone' => '0554567890', 'notes' => 'يعمل في مجال التسويق'),
            array('name' => 'هند محمود', 'email' => 'hind@example.com', 'phone' => '0556789012', 'notes' => ''),
            array('name' => 'طارق حسن', 'email' => 'tarek@example.com', 'phone' => '0551239876', 'notes' => 'أكمل الدورة بنجاح'),
            array('name' => 'لينا عادل', 'email' => 'lina@example.com', 'phone' => '0558761234', 'notes' => 'حصلت على شهادة امتياز'),
            array('name' => 'بدر فيصل', 'email' => 'badr@example.com', 'phone' => '0555432109', 'notes' => ''),
            array('name' => 'شهد ناصر', 'email' => 'shahad@example.com', 'phone' => '0552109876', 'notes' => ''),
            array('name' => 'عمر سلطان', 'email' => 'omar@example.com', 'phone' => '0559871234', 'notes' => 'طلب إيقاف مؤقت'),
            array('name' => 'جود إبراهيم', 'email' => 'jood@example.com', 'phone' => '0556543210', 'notes' => ''),
            array('name' => 'راكان عبدالله', 'email' => 'rakan@example.com', 'phone' => '0553210987', 'notes' => 'مهتم بالتعلم الآلي')
        );
        
        $student_ids = array();
        foreach ($students as $student) {
            $wpdb->insert($wpdb->prefix . 'eclass_students', $student);
            $student_ids[] = $wpdb->insert_id;
        }
        
        // تسجيل الطلاب في الدورات (النظام الجديد - دورات متعددة)
        $enrollments = array(
            // عبدالله محمد - مسجل في دورتين
            array('student_id' => $student_ids[0], 'course_id' => $course_ids[0], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-2 weeks')), 'notes' => 'دورة تطوير الويب'),
            array('student_id' => $student_ids[0], 'course_id' => $course_ids[1], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-1 week')), 'notes' => 'دورة علوم البيانات'),
            
            // نورة أحمد - دورة واحدة
            array('student_id' => $student_ids[1], 'course_id' => $course_ids[0], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-2 weeks')), 'notes' => ''),
            
            // يوسف علي - مسجل في 3 دورات
            array('student_id' => $student_ids[2], 'course_id' => $course_ids[0], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-13 days')), 'notes' => ''),
            array('student_id' => $student_ids[2], 'course_id' => $course_ids[2], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-1 week')), 'notes' => ''),
            array('student_id' => $student_ids[2], 'course_id' => $course_ids[3], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-5 days')), 'notes' => ''),
            
            // مريم خالد - دورتين
            array('student_id' => $student_ids[3], 'course_id' => $course_ids[1], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-1 week')), 'notes' => ''),
            array('student_id' => $student_ids[3], 'course_id' => $course_ids[3], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-3 days')), 'notes' => ''),
            
            // سعد عبدالرحمن - دورة واحدة
            array('student_id' => $student_ids[4], 'course_id' => $course_ids[1], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-1 week')), 'notes' => ''),
            
            // ريم سعيد - دورة واحدة
            array('student_id' => $student_ids[5], 'course_id' => $course_ids[2], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-3 weeks')), 'notes' => ''),
            
            // فهد ماجد - دورتين
            array('student_id' => $student_ids[6], 'course_id' => $course_ids[2], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-3 weeks')), 'notes' => ''),
            array('student_id' => $student_ids[6], 'course_id' => $course_ids[0], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-2 weeks')), 'notes' => ''),
            
            // هند محمود - دورة واحدة
            array('student_id' => $student_ids[7], 'course_id' => $course_ids[2], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-20 days')), 'notes' => ''),
            
            // طارق حسن - دورة مكتملة
            array('student_id' => $student_ids[8], 'course_id' => $course_ids[4], 'enrollment_status' => 'completed', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-10 weeks')), 'completion_date' => date('Y-m-d H:i:s', strtotime('-2 weeks')), 'notes' => ''),
            
            // لينا عادل - دورة مكتملة
            array('student_id' => $student_ids[9], 'course_id' => $course_ids[4], 'enrollment_status' => 'completed', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-10 weeks')), 'completion_date' => date('Y-m-d H:i:s', strtotime('-2 weeks')), 'notes' => ''),
            
            // بدر فيصل - دورتين
            array('student_id' => $student_ids[10], 'course_id' => $course_ids[0], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-10 days')), 'notes' => ''),
            array('student_id' => $student_ids[10], 'course_id' => $course_ids[1], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-1 week')), 'notes' => ''),
            
            // شهد ناصر - دورة واحدة
            array('student_id' => $student_ids[11], 'course_id' => $course_ids[1], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-5 days')), 'notes' => ''),
            
            // عمر سلطان - دورة غير نشطة
            array('student_id' => $student_ids[12], 'course_id' => $course_ids[2], 'enrollment_status' => 'inactive', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-3 weeks')), 'notes' => ''),
            
            // جود إبراهيم - دورة واحدة
            array('student_id' => $student_ids[13], 'course_id' => $course_ids[0], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-1 week')), 'notes' => ''),
            
            // راكان عبدالله - دورتين
            array('student_id' => $student_ids[14], 'course_id' => $course_ids[1], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-3 days')), 'notes' => ''),
            array('student_id' => $student_ids[14], 'course_id' => $course_ids[3], 'enrollment_status' => 'active', 'enrollment_date' => date('Y-m-d H:i:s', strtotime('-2 days')), 'notes' => '')
        );
        
        // إدراج التسجيلات في الجدول الجديد
        foreach ($enrollments as $enrollment) {
            $wpdb->insert($wpdb->prefix . 'eclass_student_courses', $enrollment);
        }
        
        // Insert Billing Records
        $billings = array(
            array(
                'invoice_number' => 'INV-2024-001',
                'student_id' => $student_ids[0],
                'course_id' => $course_ids[0],
                'amount' => 2500.00,
                'due_date' => date('Y-m-d', strtotime('-1 week')),
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'transaction_code' => 'TXN-20241001-ABC',
                'payment_date' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'notes' => 'تم الدفع كاملاً'
            ),
            array(
                'invoice_number' => 'INV-2024-002',
                'student_id' => $student_ids[1],
                'course_id' => $course_ids[0],
                'amount' => 2500.00,
                'due_date' => date('Y-m-d', strtotime('-1 week')),
                'payment_status' => 'paid',
                'payment_method' => 'credit_card',
                'transaction_code' => 'TXN-20241002-XYZ',
                'payment_date' => date('Y-m-d H:i:s', strtotime('-9 days')),
                'notes' => ''
            ),
            array(
                'invoice_number' => 'INV-2024-003',
                'student_id' => $student_ids[2],
                'course_id' => $course_ids[0],
                'amount' => 2500.00,
                'due_date' => date('Y-m-d', strtotime('+3 days')),
                'payment_status' => 'pending',
                'payment_method' => '',
                'transaction_code' => '',
                'payment_date' => null,
                'notes' => 'في انتظار الدفع'
            ),
            array(
                'invoice_number' => 'INV-2024-004',
                'student_id' => $student_ids[3],
                'course_id' => $course_ids[1],
                'amount' => 3000.00,
                'due_date' => date('Y-m-d', strtotime('-5 days')),
                'payment_status' => 'overdue',
                'payment_method' => '',
                'transaction_code' => '',
                'payment_date' => null,
                'notes' => 'متأخر عن الدفع'
            ),
            array(
                'invoice_number' => 'INV-2024-005',
                'student_id' => $student_ids[4],
                'course_id' => $course_ids[1],
                'amount' => 3000.00,
                'due_date' => date('Y-m-d'),
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'transaction_code' => 'CASH-001',
                'payment_date' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'notes' => 'دفع نقدي'
            ),
            array(
                'invoice_number' => 'INV-2024-006',
                'student_id' => $student_ids[5],
                'course_id' => $course_ids[2],
                'amount' => 2000.00,
                'due_date' => date('Y-m-d', strtotime('-2 weeks')),
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'transaction_code' => 'TXN-20240920-DEF',
                'payment_date' => date('Y-m-d H:i:s', strtotime('-3 weeks')),
                'notes' => ''
            ),
            array(
                'invoice_number' => 'INV-2024-007',
                'student_id' => $student_ids[6],
                'course_id' => $course_ids[2],
                'amount' => 2000.00,
                'due_date' => date('Y-m-d', strtotime('+1 week')),
                'payment_status' => 'pending',
                'payment_method' => '',
                'transaction_code' => '',
                'payment_date' => null,
                'notes' => ''
            ),
            array(
                'invoice_number' => 'INV-2024-008',
                'student_id' => $student_ids[8],
                'course_id' => $course_ids[4],
                'amount' => 2200.00,
                'due_date' => date('Y-m-d', strtotime('-8 weeks')),
                'payment_status' => 'paid',
                'payment_method' => 'credit_card',
                'transaction_code' => 'TXN-20240815-GHI',
                'payment_date' => date('Y-m-d H:i:s', strtotime('-9 weeks')),
                'notes' => 'دورة مكتملة'
            ),
            array(
                'invoice_number' => 'INV-2024-009',
                'student_id' => $student_ids[9],
                'course_id' => $course_ids[4],
                'amount' => 2200.00,
                'due_date' => date('Y-m-d', strtotime('-8 weeks')),
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'transaction_code' => 'TXN-20240816-JKL',
                'payment_date' => date('Y-m-d H:i:s', strtotime('-9 weeks')),
                'notes' => 'دورة مكتملة'
            ),
            array(
                'invoice_number' => 'INV-2024-010',
                'student_id' => $student_ids[10],
                'course_id' => $course_ids[0],
                'amount' => 2500.00,
                'due_date' => date('Y-m-d', strtotime('+5 days')),
                'payment_status' => 'pending',
                'payment_method' => '',
                'transaction_code' => '',
                'payment_date' => null,
                'notes' => ''
            )
        );
        
        foreach ($billings as $billing) {
            $wpdb->insert($wpdb->prefix . 'eclass_billing', $billing);
        }
        
        return array(
            'success' => true,
            'message' => sprintf(
                __('Successfully inserted sample data: %d instructors, %d courses, %d students, %d enrollments, %d invoices', 'eclass'),
                count($instructors),
                count($courses),
                count($students),
                count($enrollments),
                count($billings)
            )
        );
    }
    
    /**
     * Delete all data
     */
    public static function delete_all_data() {
        global $wpdb;
        
        // حذف بالترتيب الصحيح (الجداول التابعة أولاً)
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}eclass_billing");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}eclass_student_courses");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}eclass_students");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}eclass_courses");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}eclass_instructors");
        
        return array(
            'success' => true,
            'message' => __('All data has been deleted successfully', 'eclass')
        );
    }
}
