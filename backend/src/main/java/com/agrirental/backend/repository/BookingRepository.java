package com.agrirental.backend.repository;

import com.agrirental.backend.entity.Booking;
import com.agrirental.backend.entity.User;
import com.agrirental.backend.entity.Equipment;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface BookingRepository extends JpaRepository<Booking, Long> {
    List<Booking> findByFarmer(User farmer);
    List<Booking> findByEquipment(Equipment equipment);
}
