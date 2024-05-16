<?php
/**
 * Plugin Name: Booknetic Appointments API
 * Description: Extends Booknetic to allow creating appointments from a React Native app via a custom API.
 * Version: 1.0
 * Author: Your Name
 */

use BookneticApp\Backend\Appointments\Ajax;
use BookneticApp\Frontend\Controller\SignupAjax;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Backend\Appointments\Helpers\AppointmentRequests;
use BookneticApp\Config;
use BookneticApp\Models\AppointmentPrice;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Core\CapabilitiesException;
use BookneticApp\Providers\UI\TabUI;
use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use BookneticApp\Backend\Appointments\Helpers\AppointmentService;
use BookneticApp\Backend\Appointments\Helpers\CalendarService;
use BookneticApp\Models\Appointment;
use BookneticApp\Models\AppointmentExtra;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Location;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Models\ServiceExtra;
use BookneticApp\Models\ServiceStaff;
use BookneticApp\Models\Staff;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Core\Permission;


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );




// add_action('rest_api_init', function () {
//     register_rest_route('myapi/v1', '/list_appointments_public/', [
//         'methods' => 'GET',
//         'callback' => 'list_appointments_api_callback_public',
//         'permission_callback' => '__return_true'  // Adjust the permissions as needed
//     ]);
// });



// function list_appointments_api_callback_public(WP_REST_Request $request) {
//     // Query the Booknetic Appointments


	
//      $current_user = wp_get_current_user();
	
// 	 $user_id = apply_filters('determine_current_user', false);
	
// 	 $user_data = get_userdata($user_id);

   
//      $user_email = $user_data->user_email;

	

//     $booknetic_user = Customer::where('email', $user_email  )->fetch();

// 	$appointments = Appointment::fetchAll();

	
//     $appointments_list = [];
//     foreach ($appointments as $appointment) {
//         // Fetch related entities manually
//         $customer = Customer::get($appointment->customer_id);
//         $service = Service::get($appointment->service_id);
//         $staff = Staff::get($appointment->staff_id);
//         $location = Location::get($appointment->location_id);

//         // Adjust the structure as needed
//         $appointments_list[] = [
//             'id' => $appointment->id,
//             'appointment->customer_id' => $appointment->customer_id ,
//             'appointment->service_id' => $appointment->service_id ,
//             'appointment->staff_id' => $appointment->staff_id ,
//             'appointment->location_id' => $appointment->location_id ,
//             'customer_name' => $customer ? $customer->first_name . ' ' . $customer->last_name : 'N/A',
// 			'booknetic_user' => $booknetic_user,
//             'service_name' => $service ? $service->name : 'N/A',
//             'staff_name' => $staff ? $staff->name : 'N/A',
// 			'customer_id' =>$appointment->customer_id,
// 			'myuseeeer_emaaail' => $current_user ,
//             'location_name' => $location ? $location->name : 'N/A',
// 			'date' => date('Y-m-d H:i:s',  $appointment->starts_at ),
// 			'end' => date('Y-m-d H:i:s',  $appointment->ends_at ),
//             'status' => $appointment->status,
// 			'$user_id' => 	    $user_id ,
// 			'$user_email' => $user_email ,
//             'note' => $appointment->note,
//             // Include any other fields as needed
//         ];
//     }

//     return new WP_REST_Response($appointments_list, 200);
// }




// ___________________________________________________________________________________________________________________________________________



add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/list_appointments_current_user/', [
        'methods' => 'GET',
        'callback' => 'list_appointments_api_callback_current_user',
        'permission_callback' => '__return_true'
    ]);
});




function list_appointments_api_callback_current_user(WP_REST_Request $request) {

	
     $current_user = wp_get_current_user();
	
	 $user_id = apply_filters('determine_current_user', false);
	
	 $user_data = get_userdata($user_id);

     $user_email = $user_data->user_email;

     if(!isset($user_email)){
        return new WP_REST_Response('403 - Forbidden', 403);
     }

    $booknetic_user = Customer::where('email', $user_email  )->fetch();
	$appointments = Appointment::where( 'customer_id', $booknetic_user->id )->fetchAll();

	
    $appointments_list = [];
    foreach ($appointments as $appointment) {

        $customer = Customer::get($appointment->customer_id);
        $service = Service::get($appointment->service_id);
        $staff = Staff::get($appointment->staff_id);
        $location = Location::get($appointment->location_id);


        $appointments_list[] = [
            'id' => $appointment->id,
            'customer_name' => $customer ? $customer->first_name . ' ' . $customer->last_name : 'N/A',
			'booknetic_user' => $booknetic_user,
            'service_name' => $service ? $service->name : 'N/A',
            'staff_name' => $staff ? $staff->name : 'N/A',
			'customer_id' =>$appointment->customer_id,
			'myuseeeer_emaaail' => $current_user ,
            'location_name' => $location ? $location->name : 'N/A',
			'date' => date('Y-m-d H:i:s',  $appointment->starts_at ),
			'end' => date('Y-m-d H:i:s',  $appointment->ends_at ),
            'status' => $appointment->status,
			'$user_id' => 	    $user_id ,
			'$user_email' => $user_email ,
            'note' => $appointment->note,
        ];
    }

    return new WP_REST_Response($appointments_list, 200);
}


// ___________________________________________________________________________________________________________________________________________


add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/create_appointment/', [
        'methods' => 'POST',
        'callback' => 'create_appointment_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function create_appointment_api_callback(WP_REST_Request $request) {



    // $Ajax = new Ajax();

    // $rsult = $Ajax->create_appointment();

	
    $user_id = apply_filters('determine_current_user', false);
   
    $user_data = get_userdata($user_id);

    $user_email = $user_data->user_email;

    $booknetic_user = Customer::where('email', $user_email  )->fetch();
   
     if(!isset($booknetic_user->id)){
       return new WP_REST_Response('403 - Forbidden', 403);
    }

    $Ajax = new Ajax();

    $rsult = $Ajax->create_appointment();


    return new WP_REST_Response( $rsult , 200);
}

/*

FORM DATA

current: 0
cart: [{"location":"1","service":"5","staff":"4","date":"2024-04-04","time":"16:55","note":"","recurring_start_date":"2024-04-04","recurring_end_date":"2024-05-04","recurring_times":{},"customer_id":"2","status":"pending","weight":"1","service_extras":[]}]
run_workflows: 1
module: appointments
action: create_appointment

*/





// ___________________________________________________________________________________________________




add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/register_new_user/', [
        'methods' => 'POST',
        'callback' => 'register_new_user_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function register_new_user_api_callback(WP_REST_Request $request) {

    //  $Ajax = new Ajax();

    // $rsult = $Ajax->create_appointment();

    $current_user = wp_get_current_user();
	
    $user_id = apply_filters('determine_current_user', false);
   
    $user_data = get_userdata($user_id);

    $user_email = $user_data->user_email;

    // if(!isset($user_email)){
    //    return new WP_REST_Response('403 - Forbidden', 403);
    // }

   $booknetic_user = Customer::where('email', $user_email  )->fetch();

   $SignupAjax = new SignupAjax();

   $result = $SignupAjax->signup();
 
    return new WP_REST_Response( $result , 200);
}

/*

FORM DATA

id: 0
first_name: testuser10
last_name: testuser10
gender: male
birthday: 
phone: 
email: testuser10@test.test
allow_customer_to_login: 1
wp_user_use_existing: no
wp_user_password: testuser10
note: 
image: undefined
extras: []
module: customers
action: save_customer

*/



// ___________________________________________________________________________________________________




add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/edit_appointment/', [
        'methods' => 'POST',
        'callback' => 'edit_appointment_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function edit_appointment_api_callback(WP_REST_Request $request) {

     $Ajax = new Ajax();

    $result = $Ajax->save_edited_appointment();

    return new WP_REST_Response($result , 200);
}

/*

FORM DATA

run_workflows: 1
current: 0
cart: [{"id":3,"location":"3","service":"5","staff":"3","date":"2024-04-18","time":"09:00","note":"","customer_id":"1","status":"pending","weight":"1","service_extras":[]}]
module: appointments
action: save_edited_appointment

*/


// ___________________________________________________________________________________________________




add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/info_appointment/', [
        'methods' => 'POST',
        'callback' => 'info_appointment_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function info_appointment_api_callback(WP_REST_Request $request) {

    Capabilities::must( 'appointments' );

    $id = Helper::_post('id', '0', 'integer');

    $appointmentInfo = Appointment::leftJoin( 'customer', ['first_name', 'last_name', 'phone_number', 'email', 'profile_image'])
        ->leftJoin( 'location', ['name'] )
        ->leftJoin( 'service', ['name'] )
        ->leftJoin( 'staff', ['name', 'profile_image', 'email', 'phone_number'])
        ->where( Appointment::getField('id'), $id )->fetch();

    if( !$appointmentInfo )
    {
        
        return Helper::response( false , bkntc__('Appointment not found!') );
    }

    $extrasArr = AppointmentExtra::where('appointment_id', $id)
        ->leftJoin(ServiceExtra::class, ['name', 'image'], ServiceExtra::getField('id'), AppointmentExtra::getField('extra_id'))
        ->fetchAll();


    $paymentGatewayList = [];
    $appointmentPrice = AppointmentPrice::where('appointment_id',  $appointmentInfo->id)
        ->select('sum(price * negative_or_positive) as total_amount', true)->fetch();

    if( $appointmentPrice->total_amount != $appointmentInfo->paid_amount )
    {
        $paymentGatewayList = PaymentGatewayService::getInstalledGatewayNames();
        $paymentGatewayList = array_filter($paymentGatewayList , function ($paymentGateway){
            return property_exists( PaymentGatewayService::find($paymentGateway) , 'createPaymentLink');
        });
    }

    return new WP_REST_Response(
        
        ['appointmentInfo' => $appointmentInfo, 'extrasArr' => $extrasArr  , 'paymentGatewayList' => $paymentGatewayList], 200);
}

/*

FORM DATA

id: 4
module: appointments
action: info
_mn: 4

*/


// ___________________________________________________________________________________________________


add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/get_service/', [
        'methods' => 'POST',
        'callback' => 'service_appointment_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function service_appointment_api_callback(WP_REST_Request $request) {

     $Ajax = new Ajax();

    $result = $Ajax->get_services();

    return new WP_REST_Response($result , 200);
}

/*

FORM DATA

module: appointments
action: get_services
category: 3

*/


// ___________________________________________________________________________________________________



add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/get_locations/', [
        'methods' => 'POST',
        'callback' => 'locations_appointment_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function locations_appointment_api_callback(WP_REST_Request $request) {

     $Ajax = new Ajax();

    $result = $Ajax->get_locations();

    return new WP_REST_Response($result , 200);
}

/*

FORM DATA

module: appointments
action: get_locations

*/



// ___________________________________________________________________________________________________





add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/get_service_categories/', [
        'methods' => 'POST',
        'callback' => 'get_service_categories_appointment_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function get_service_categories_appointment_api_callback(WP_REST_Request $request) {

     $Ajax = new Ajax();

    $result = $Ajax->get_service_categories();

    return new WP_REST_Response($result , 200);
}

/*

FORM DATA

module: appointments
action: get_service_categories

*/



// ___________________________________________________________________________________________________




add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/get_staff/', [
        'methods' => 'POST',
        'callback' => 'get_staff_appointment_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function get_staff_appointment_api_callback(WP_REST_Request $request) {

     $Ajax = new Ajax();

    $result = $Ajax->get_staff();

    return new WP_REST_Response($result , 200);
}

/*

FORM DATA

module: appointments
action: get_staff
location: 1
service: 5

*/


// ___________________________________________________________________________________________________




add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/get_customers/', [
        'methods' => 'POST',
        'callback' => 'get_customers_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function get_customers_api_callback(WP_REST_Request $request) {

     $Ajax = new Ajax();

    $result = $Ajax->get_customers();

    return new WP_REST_Response($result , 200);
}

/*

FORM DATA

module: appointments
action: get_customers

*/




// ___________________________________________________________________________________________________



add_action('rest_api_init', function () {
    register_rest_route('myapi/v1', '/get_available_times/', [
        'methods' => 'POST',
        'callback' => 'get_available_times_api_callback',
        'permission_callback' => '__return_true'  // Adjust the permissions as needed
    ]);
});



function get_available_times_api_callback(WP_REST_Request $request) {

     $Ajax = new Ajax();

    $result = $Ajax->get_available_times();

    return new WP_REST_Response($result , 200);
}

/*

FORM DATA

module: appointments
action: get_available_times
id: 4
service: 5
location: 1
service_extras: []
staff: 4
date: 2024-06-04

*/





// ___________________________________________________________________________________________________






// add_action('rest_api_init', function () {
//     register_rest_route('myapi/v1', '/get_available_times_all/', [
//         'methods' => 'POST',
//         'callback' => 'get_available_all_times_api_callback',
//         'permission_callback' => '__return_true'  // Adjust the permissions as needed
//     ]);
// });



// function get_available_all_times_api_callback(WP_REST_Request $request) {

//      $Ajax = new Ajax();

//     $result = $Ajax->get_available_times_all();

//     return new WP_REST_Response($result , 200);
// }

// /*

// FORM DATA

// module: appointments
// action: get_available_times
// id: 4
// service: 5
// location: 1
// service_extras: []
// staff: 4
// date: 2024-06-04

// */





// ___________________________________________________________________________________________________