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
}

export type UnitType = {
    id: number;
    name: string;
}

export type UsageStatusType = {
    id: number;
    name: string;
}

export type LocationType = {
    id: number;
    name: string;
}

export type AcquisitionMethodType = {
    id: number;
    name: string;
}

export type InspectionType = {
    id: number;
    item_id: number;
    inspection_scheduled_date: Date | null;
    inspection_date: Date | null;
    inspection_person: string | null;
    details: string | null;
    created_at: Date;
}

export type DisposalType = {
    id: number;
    item_id: number;
    disposal_scheduled_date: Date | null;
    disposal_date: Date | null;
    disposal_person: string | null;
    details: string | null;
    created_at: Date;
}
