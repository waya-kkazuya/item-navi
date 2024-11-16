export type ItemType = { 
    id: number; 
    management_id: string;
    name: string;
    category_id: number;
    image1: string;
    image_path1: string;
    stock: number;
    unit_id: number;
    minimum_stock: number;
    notification: boolean;
    usage_status_id: number;
    end_user: string;
    location_of_use_id: number;
    storage_location_id: number;
    acquisition_method_id: number;
    acquisition_source: string;
    price: number;
    date_of_acquisition: Date; 
    manufacturer: string;
    product_number: string;
    remarks: string;
    qrcode: string;
    deleted_at: Date | null;
    created_at: Date;
    inspection_scheduled_date: Date | null;
    category: CategoryType;
    unit: UnitType;
    usage_status: UsageStatusType;
    location_of_use: LocationType;
    storage_location: LocationType;
    acquisition_method: AcquisitionMethodType;
    inspections: InspectionType;
    disposal: DisposalType;
};

export type CategoryType = {
    id: number;
    name: string;
};

export type UnitType = {
    id: number;
    name: string;
};

export type UsageStatusType = {
    id: number;
    name: string;
};

export type LocationType = {
    id: number;
    name: string;
};

export type AcquisitionMethodType = {
    id: number;
    name: string;
};

export type InspectionType = {
    id: number;
    item_id: number;
    inspection_scheduled_date: Date | null;
    inspection_date: Date | null;
    inspection_person: string | null;
    details: string | null;
    created_at: Date;
};

export type DisposalType = {
    id: number;
    item_id: number;
    disposal_scheduled_date: Date | null;
    disposal_date: Date | null;
    disposal_person: string | null;
    details: string | null;
    created_at: Date;
};

export type EditReasonType = {
    id: number;
    reason: string;
};

export type ItemRequestType = {
    id: number;
    request_status_id: number;
    request_status: RequestStatusType;
    name: string;
    category: CategoryType;
    category_id: number;
    location_of_use_id: number;
    location_of_use: LocationType;
    requestor: string;
    remarks_from_requestor: string;
    manufacturer: string | null;
    reference: string | null;
    price: number;
    created_at: Date;
    formatted_created_at: Date;
};

export type RequestStatusType = {
    id: number;
    status_name: string;
};

export type EditHistoryType = {
    id: number;
    edit_mode: string;
    operation_type: string;
    item_id: number;
    item: ItemType;
    edited_field: string | null;
    old_value: string | null;
    new_value: string | null;
    edit_user: string | null;
    edit_reason_id: number | null;
    edit_reason: EditReasonType;
    edit_reason_text: string | null;
    created_at: string;
    date: string;
    day_of_week: string;
    time: string;
    operation_description: string;
    operation_type_for_display: string;
    edited_field_for_display: string;
};

export type NotificationType = {
    id: string;
    item_id: number;
    type: string;
    notifiable_type: string;
    notifiable_id: number;
    data: Record<string, any>;
    read_at: string | null;
    created_at: string;
    relative_time: string;
};

export type UserType = {
    id: number;
    name: string;
    email: string;
    role: number;
    profile_image: string | null;   
};

export type StockTransactionType = {
    id: number;
    item_id: number;
    transaction_type: string;
    quantity: number;
    operator_name: string;
    transaction_date: string;
    created_at: string;
    current_stock: number;
};