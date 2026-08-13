<?php
// helper function to get final booking details (used by staff, admin, customer [payment & view details page])
function getBookingDetails($conn, $type, $finalBookingId) {
    $result = null;

    if($type === 'tour') {
        $result = fetchOne($conn, "
            SELECT fb.total_amount, fb.notes, fb.booking_id, p.pkg_name,
                   GROUP_CONCAT(DISTINCT g.full_name SEPARATOR ', ') AS guides,
                   GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS transports,
                   GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS accommodations
            FROM final_booking fb
            JOIN booking_service bs ON fb.final_booking_id = bs.final_booking_id
            JOIN tour_booking tb ON fb.booking_id = tb.booking_id
            JOIN tour_packages p ON tb.pkg_id = p.pkg_id
            LEFT JOIN guides g ON bs.guide_id = g.guide_id
            LEFT JOIN transport t ON bs.transport_id = t.transport_id
            LEFT JOIN accommodation a ON bs.accommodation_id = a.accommodation_id
            WHERE fb.final_booking_id = ?
            GROUP BY fb.final_booking_id, fb.total_amount, fb.notes, fb.booking_id, p.pkg_name
        ", "i", [$finalBookingId]);

    } elseif($type === 'customized') {
        $result = fetchOne($conn, "
            SELECT fb.total_amount, fb.notes, fb.cpkg_id,
                   ct.destination_notes, ct.activity_notes,
                   GROUP_CONCAT(DISTINCT g.full_name SEPARATOR ', ') AS guides,
                   GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS transports,
                   GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS accommodations
            FROM final_booking fb
            JOIN booking_service bs ON fb.final_booking_id = bs.final_booking_id
            JOIN customized_tours ct ON fb.cpkg_id = ct.cpkg_id
            LEFT JOIN guides g ON bs.guide_id = g.guide_id
            LEFT JOIN transport t ON bs.transport_id = t.transport_id
            LEFT JOIN accommodation a ON bs.accommodation_id = a.accommodation_id
            WHERE fb.final_booking_id = ?
            GROUP BY fb.final_booking_id, fb.total_amount, fb.notes, fb.cpkg_id, ct.destination_notes, ct.activity_notes
        ", "i", [$finalBookingId]);

    } elseif($type === 'transport') {
        $result = fetchOne($conn, "
            SELECT fb.total_amount, fb.notes,
                   GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS transports,
                   GROUP_CONCAT(DISTINCT t.vehicle_type SEPARATOR ', ') AS vehicle_type
            FROM final_booking fb
            JOIN booking_service bs ON fb.final_booking_id = bs.final_booking_id
            JOIN transport t ON bs.transport_id = t.transport_id
            WHERE fb.final_booking_id = ?
            GROUP BY fb.final_booking_id, fb.total_amount, fb.notes
        ", "i", [$finalBookingId]);

    } elseif($type === 'accommodation') {
        $result = fetchOne($conn, "
            SELECT fb.total_amount, fb.notes,
                   GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS accomm_name
            FROM final_booking fb
            JOIN booking_service bs ON fb.final_booking_id = bs.final_booking_id
            JOIN accommodation a ON bs.accommodation_id = a.accommodation_id
            WHERE fb.final_booking_id = ?
            GROUP BY fb.final_booking_id, fb.total_amount, fb.notes
        ", "i", [$finalBookingId]);
    }

    return $result;
}
?>