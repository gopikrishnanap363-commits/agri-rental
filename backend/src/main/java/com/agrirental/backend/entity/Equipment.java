package com.agrirental.backend.entity;

import com.agrirental.backend.entity.enums.EquipmentApprovalStatus;
import com.agrirental.backend.entity.enums.EquipmentAvailabilityStatus;
import jakarta.persistence.*;
import lombok.Data;
import lombok.ToString;

import java.math.BigDecimal;
import java.util.List;

@Entity
@Table(name = "equipment")
@Data
public class Equipment {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "owner_id", referencedColumnName = "id", nullable = false)
    @ToString.Exclude
    private User owner;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "category_id", referencedColumnName = "id", nullable = false)
    @ToString.Exclude
    private EquipmentCategory category;

    @Column(name = "equipment_name", nullable = false)
    private String equipmentName;

    private String brand;

    @Column(name = "manufacturing_year")
    private Integer manufacturingYear;

    @Column(name = "price_per_day", nullable = false)
    private BigDecimal pricePerDay;

    @Column(nullable = false)
    private String location;

    @Column(name = "primary_image")
    private String primaryImage;

    @Column(name = "rating_avg")
    private Double ratingAvg;

    @Enumerated(EnumType.STRING)
    @Column(name = "approval_status", nullable = false)
    private EquipmentApprovalStatus approvalStatus;

    @Enumerated(EnumType.STRING)
    @Column(name = "availability_status", nullable = false)
    private EquipmentAvailabilityStatus availabilityStatus;

    @OneToMany(mappedBy = "equipment", cascade = CascadeType.ALL, fetch = FetchType.LAZY)
    @ToString.Exclude
    private List<Booking> bookings;
}
