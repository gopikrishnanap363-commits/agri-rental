package com.agrirental.backend.entity;

import jakarta.persistence.*;
import lombok.Data;
import lombok.ToString;

@Entity
@Table(name = "farmer_profiles")
@Data
public class FarmerProfile {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @OneToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", referencedColumnName = "id", nullable = false)
    @ToString.Exclude
    private User user;

    @Column(name = "farm_size_acres")
    private Double farmSizeAcres;

    @Column(name = "soil_type")
    private String soilType;

    @Column(name = "primary_crops")
    private String primaryCrops;
}
