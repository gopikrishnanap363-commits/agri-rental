package com.agrirental.backend.repository;

import com.agrirental.backend.entity.Equipment;
import com.agrirental.backend.entity.User;
import com.agrirental.backend.entity.EquipmentCategory;
import com.agrirental.backend.entity.enums.EquipmentApprovalStatus;
import com.agrirental.backend.entity.enums.EquipmentAvailabilityStatus;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface EquipmentRepository extends JpaRepository<Equipment, Long> {
    List<Equipment> findByOwner(User owner);
    List<Equipment> findByCategory(EquipmentCategory category);
    List<Equipment> findByAvailabilityStatus(EquipmentAvailabilityStatus status);
    List<Equipment> findByApprovalStatus(EquipmentApprovalStatus status);
}
